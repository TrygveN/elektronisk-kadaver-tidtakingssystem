<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="matcha.css">
    <style>
      body {
        padding: 0;
      }
      .settings {
        padding: 0 1.5rem;
      }
      fieldset {
          display: flex;
          flex-wrap: wrap;
          justify-content: space-evenly;
      }
      .bg-success {
        background: var(--bg-success) !important;
      }
      .bg-active {
        background: var(--bg-active) !important;
      }
    </style>
</head>
<body>
  <h2>Simple Form</h2>
  <form action="/runner.php" method="POST">
    <label for="password">Passord:</label><br>
    <input type="text" id="password" name="password" required><br><br>

    <label for="id">Startnummer:</label><br>
    <input type="text" id="id" name="id" required><br><br>

    <label for="navn">Navn:</label><br>
    <input type="text" id="name" name="input2"><br><br>

    <button type="submit">Submit</button>
  </form>

</div>
</body>
</html>