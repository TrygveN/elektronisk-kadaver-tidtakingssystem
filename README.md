# Elektronisk Kadaver Tidtakingssystem (EKT)

Enkelt tidtakingssystem for Kadaverløpet. All data lagres i tekstfiler, og PHP
kverner `data/db.csv` (løpere) og `data/passering.csv` (registrerte passeringer)
om til resultatlister.

## Krav

- PHP 8.1 eller nyere med `curl` og støtte for `PASSWORD_ARGON2ID`.
- Skrivetilgang til `data/` for webserveren.

## Oppsett

Alle datafiler ligger i `data/`, som er gitignorert. Lag mappa og filene manuelt:

```sh
mkdir -p data
```

1. **Passord** – generer admin-hash (kjøres fra kommandolinjen):

   ```sh
   php set_password.php
   ```

   Dette skriver `data/hash.php`. Passordet kan også gis som argument eller via
   `EKT_PASSWORD`.

2. **Konfigurasjon** – `data/config.ini` med starttid for begge løypene:

   ```ini
   start_date = "2025-11-01T09:00:01+01"
   start_date_mini = "2025-11-01T11:25:46+01"
   skjul_resultater = false
   ```

   Sett `skjul_resultater = true` for å skjule resultattabellene på forsiden.
   Resultatene i admin-grensesnittet påvirkes ikke.

3. **Løperbase** – `data/db.csv`. Første linje er header; feltene er
   `;`-separert i samme rekkefølge som Nettskjema-eksporten:

   ```csv
   "$submission_id";"$created";"Navn";"E-postadresse";"Telefonnummer";"Lag, forening eller kollektiv";"Kadaverløpet eller Minikadaver'n?";"Student?";"$answer_time_ms";"Startet";"Betalt"
   ```

4. **Passeringer** – `data/passering.csv` kan være tom. Format per linje:
   `post,startnummer,tidspunkt` (post 1–3, tid på ISO 8601).

5. **Nettskjema (valgfritt)** – `data/nettskjema.ini` for synk av påmeldinger:

   ```ini
   client_id = "..."
   client_secret = "..."
   form_id = "..."
   ```

   Token caches i `data/nettskjema_token.json`.

## Kjøre lokalt

```sh
php -S localhost:3000 -t .
```

Åpne så `http://localhost:3000/admin/` (innlogging) eller
`http://localhost:3000/index.php` (offentlige resultater).

## Filer

- `index.php` – offentlige live-resultater.
- `admin/` – innlogging og admin-grensesnitt, inkludert registrering, løperbase,
  start/betaling, konfigurasjon og nedtelling.
- `admin/nav.php` – felles navigasjonsmeny for admin-sidene.
- `api/` – endepunktene som grensesnittene kaller.
- `import_runners.php` – leser CSV-filene og definerer `Runner`.
- `set_password.php` – setter admin-passord (kun CLI).

## Sikkerhet

`data/` inneholder passordhash og eventuelle hemmeligheter og er derfor
gitignorert. Admin-innlogging skjer i nettleseren (`localStorage`), og
skriveendepunktene verifiserer passordet på serversiden.
