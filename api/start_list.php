<?php
// Eksporterer startlista som PDF (A4) med ei tabell per løype, sortert på startnummer.
$documentRoot = $_SERVER['DOCUMENT_ROOT'];
include("$documentRoot/import_runners.php");
include("$documentRoot/data/hash.php");
require_once("$documentRoot/lib/fpdf.php");

if ($_SERVER['REQUEST_METHOD'] !== "GET") {
    http_response_code(405);
    header("Allow: GET");
    exit("Bruk GET.");
}

$password = rawurldecode($_SERVER['HTTP_PASSWORD'] ?? "");
if (!password_verify($password, $hash)) {
    http_response_code(401);
    exit("Feil passord.");
}

$config = parse_ini_file("$documentRoot/data/config.ini") ?: [];
date_default_timezone_set('UTC');
$start = DateTime::createFromFormat(DateTime::ISO8601, $config['start_date'] ?? "");
$year = $start ? $start->format('Y') : date('Y');

$runners = read_runners_from_csv();

// Tekst i FPDF sine innebygde skrifter må vere ISO-8859-1 (æøå går bra).
function pdf_text($value)
{
    return mb_convert_encoding((string) $value, 'ISO-8859-1', 'UTF-8');
}

// Startlista skal ha ei rad per løpar, sortert på startnummer.
function runners_for_course(array $runners, string $course)
{
    $list = array_values(array_filter($runners, fn($runner) => $runner->course === $course));
    usort($list, fn($a, $b) => $a->id <=> $b->id);
    return $list;
}

class StartListPdf extends FPDF
{
    public function Footer()
    {
        $this->SetY(-12);
        $this->SetFont('DejaVu', '', 9);
        $this->SetTextColor(110, 110, 110);
        $this->Cell(0, 5, pdf_text("Side ") . $this->PageNo() . ' av {nb}', 0, 0, 'C');
    }

    public function FontSizeMm()
    {
        return $this->FontSizePt * 25.4 / 72;
    }

    public function EmojiWidth()
    {
        return $this->FontSizeMm() * 1.1;
    }
}

// Emoji kan ikkje skrivast direkte med FPDF, så dei blir tekna som bilde.
const PDF_EMOJI_RE = '[\x{1F000}-\x{1FAFF}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}\x{203C}\x{2049}\x{3030}\x{303D}\x{3297}\x{3299}]'
    . '[\x{FE0F}\x{20E3}\x{200D}\x{1F3FB}-\x{1F3FF}\x{1F000}-\x{1FAFF}\x{2300}-\x{23FF}\x{2600}-\x{27BF}\x{2B00}-\x{2BFF}]*';
const PDF_EMOJI_FONT = __DIR__ . '/../lib/font/NotoEmoji-Regular.ttf';

// Deler teksten i [er_emoji, tekst]-par.
function pdf_text_parts(string $text)
{
    $pieces = preg_split('/(' . PDF_EMOJI_RE . ')/u', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
    $parts = [];
    foreach ($pieces as $index => $piece) {
        if ($piece !== '') {
            $parts[] = [$index % 2 === 1, $piece];
        }
    }
    return $parts;
}

function pdf_emoji_image(string $cluster)
{
    static $images = [];
    if (array_key_exists($cluster, $images)) {
        return $images[$cluster];
    }
    $images[$cluster] = null;
    if (!function_exists('imagecreatetruecolor') || !is_file(PDF_EMOJI_FONT)) {
        return null;
    }

    $char = mb_substr(preg_replace('/[\x{FE0F}\x{200D}\x{1F3FB}-\x{1F3FF}]/u', '', $cluster), 0, 1, 'UTF-8');
    // GD leser ikkje 4-byte UTF-8, så emoji utanfor BMP er lagde i privat bruk (U+E000–U+EAFF) i skrifta.
    $code = mb_ord($char, 'UTF-8');
    if ($code >= 0x1F000 && $code <= 0x1FAFF) {
        $char = mb_chr(0xE000 + $code - 0x1F000, 'UTF-8');
    }
    $size = 256;
    $image = imagecreatetruecolor($size, $size);
    imagesavealpha($image, true);
    imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
    $box = imagettfbbox(200, 0, PDF_EMOJI_FONT, $char);
    $x = ($size - ($box[2] - $box[0])) / 2 - $box[0];
    $y = ($size - ($box[1] - $box[7])) / 2 - $box[7];
    imagettftext($image, 200, 0, (int) $x, (int) $y, imagecolorallocate($image, 0, 0, 0), PDF_EMOJI_FONT, $char);

    $path = tempnam(sys_get_temp_dir(), 'emoji');
    if ($path !== false) {
        imagepng($image, $path);
        register_shutdown_function(fn() => @unlink($path));
        $images[$cluster] = $path;
    }
    imagedestroy($image);
    return $images[$cluster];
}

function pdf_width(StartListPdf $pdf, string $text)
{
    $width = 0;
    foreach (pdf_text_parts($text) as [$is_emoji, $piece]) {
        $width += $is_emoji ? $pdf->EmojiWidth() : $pdf->GetStringWidth(pdf_text($piece));
    }
    return $width;
}

// Skriv ei tabellcelle med tekst og eventuelle emoji. Flyttar pennen til høgre for cella.
function pdf_cell(StartListPdf $pdf, float $width, string $text, string $align, bool $fill)
{
    $height = PDF_ROW_HEIGHT;
    $x = $pdf->GetX();
    $y = $pdf->GetY();
    if (!preg_match('/' . PDF_EMOJI_RE . '/u', $text)) {
        $pdf->Cell($width, $height, pdf_text($text), 'B', 0, $align, $fill);
        return;
    }

    $pdf->Cell($width, $height, '', 'B', 0, '', $fill);
    $total = pdf_width($pdf, $text);
    $cursor = match ($align) {
        'C' => $x + ($width - $total) / 2,
        'R' => $x + $width - 1 - $total,
        default => $x + 1,
    };
    $baseline = $y + $height / 2 + 0.36 * $pdf->FontSizeMm();
    foreach (pdf_text_parts($text) as [$is_emoji, $piece]) {
        if ($is_emoji) {
            $size = $pdf->EmojiWidth();
            $image = pdf_emoji_image($piece);
            if ($image !== null) {
                $pdf->Image($image, $cursor, $y + ($height - $size) / 2, $size, $size, 'png');
            }
            $cursor += $size;
        } else {
            $pdf->Text($cursor, $baseline, pdf_text($piece));
            $cursor += $pdf->GetStringWidth(pdf_text($piece));
        }
    }
    $pdf->SetXY($x + $width, $y);
}

const PDF_ROW_HEIGHT = 7;
const PDF_BOTTOM_LIMIT = 280; // Høgste y før sideskift (A4 høgd 297 mm, med margin nedst).
const PDF_COLUMNS = [
    ['Startnummer', 28, 'C'],
    ['Navn', 82, 'L'],
    ['Klubb/forening', 70, 'L'],
];

// Kortar teksten med ... dersom han ikkje får plass i kolonna.
function pdf_fit(StartListPdf $pdf, $value, float $width)
{
    $text = (string) $value;
    if (pdf_width($pdf, $text) <= $width - 2) {
        return $text;
    }
    while ($text !== "" && pdf_width($pdf, $text . "...") > $width - 2) {
        $text = mb_substr($text, 0, -1, 'UTF-8');
    }
    return $text . "...";
}

function pdf_table_header(StartListPdf $pdf)
{
    $pdf->SetFont('DejaVu', 'B', 10);
    $pdf->SetFillColor(225, 230, 236);
    $pdf->SetTextColor(0, 0, 0);
    foreach (PDF_COLUMNS as [$label, $width, $align]) {
        $pdf->Cell($width, PDF_ROW_HEIGHT, pdf_text($label), 'TB', 0, $align, true);
    }
    $pdf->Ln(PDF_ROW_HEIGHT);
}

function pdf_new_page_if_needed(StartListPdf $pdf, float $height)
{
    if ($pdf->GetY() + $height > PDF_BOTTOM_LIMIT) {
        $pdf->AddPage();
        return true;
    }
    return false;
}

function pdf_course_table(StartListPdf $pdf, string $course, array $runners)
{
    // Overskrift og første rader skal ikkje bli liggjande aleine nederst på sida.
    pdf_new_page_if_needed($pdf, 40);

    $count = count($runners);
    $count_label = $count === 1 ? "1 påmeldt" : "$count påmeldte";

    $width = array_sum(array_column(PDF_COLUMNS, 1));
    $pdf->SetFont('DejaVu', '', 10);
    $count_width = $pdf->GetStringWidth(pdf_text($count_label)) + 1;

    $pdf->SetFont('DejaVu', 'B', 14);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->Cell($width - $count_width, 9, pdf_text($course), 0, 0, 'L');
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->SetTextColor(90, 90, 90);
    $pdf->Cell($count_width, 9, pdf_text($count_label), 0, 1, 'R');
    $pdf->Ln(2);

    if ($count === 0) {
        $pdf->SetFont('DejaVu', '', 10);
        $pdf->SetTextColor(90, 90, 90);
        $pdf->Cell(0, PDF_ROW_HEIGHT, pdf_text("Ingen påmeldte."), 0, 1);
        $pdf->Ln(6);
        return;
    }

    pdf_table_header($pdf);
    $pdf->SetFont('DejaVu', '', 10);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetDrawColor(200, 200, 200);

    foreach ($runners as $index => $runner) {
        if (pdf_new_page_if_needed($pdf, PDF_ROW_HEIGHT)) {
            pdf_table_header($pdf);
            $pdf->SetFont('DejaVu', '', 10);
        }
        $fill = $index % 2 === 1;
        $pdf->SetFillColor(246, 248, 250);
        $values = [(int) $runner->id, $runner->name, $runner->club];
        foreach (PDF_COLUMNS as $column => [, $width, $align]) {
            pdf_cell($pdf, $width, pdf_fit($pdf, $values[$column], $width), $align, $fill);
        }
        $pdf->Ln(PDF_ROW_HEIGHT);
    }
    $pdf->Ln(6);
}

$pdf = new StartListPdf('P', 'mm', 'A4');
$pdf->AddFont('DejaVu', '', 'dejavusans.json');
$pdf->AddFont('DejaVu', 'B', 'dejavusansb.json');
$pdf->SetTitle("Startliste Kadaver'n $year", true);
$pdf->SetAuthor("Kadaver'n", true);
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(false);
$pdf->AliasNbPages('{nb}');
$pdf->AddPage();

$pdf->SetFont('DejaVu', 'B', 18);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 10, pdf_text("Startliste Kadaver'n $year"), 0, 1);
$pdf->Ln(4);

$courses = [
    "Kadaverløpet",
    "Minikadaver'n",
];
foreach ($courses as $index => $course) {
    if ($index > 0) {
        $pdf->AddPage();
    }
    pdf_course_table($pdf, $course, runners_for_course($runners, $course));
}

$pdf->Output('D', 'startliste-kadaver.pdf');
