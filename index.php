<!DOCTYPE html>
<html lang="no">
<script>
function register_runner(id) {
    fetch("upload.php", {
        method: "POST",
        body: 'id='+id,
        headers: {
            "Content-type": "application/x-www-form-urlencoded"
        }
    });
};
</script>
<body>
<button onclick="register_runner('121321321')">registrer!</button>
</body>
<html>