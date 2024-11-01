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
  <div class="settings">
<legend>Passord</legend>
      <input type="password" name="passord" id="password">
<fieldset>
    <legend>Velg post</legend>
    <label>
      <input type="radio" name="post" value="1" onclick="update()">
      1. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="2" onclick="update()">
      2. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="3" onclick="update()">
      Mål
    </label>
  </fieldset>

<button onmousedown="update()">Oppdater tabell</button>
<input id="search" type="number" class="form-control"  onkeyup="filterTable()"  placeholder="Søk">
<br>
</div>
<table id="runners"></table>
<script>
// Hvilken matpost vi er på:
var control = location.search[1];

function get_control() {
    try {
      return document.querySelector('input[name="post"]:checked').value;
    }
    catch (error) {
      return 0;
    }
}

function register_runner(id) {
    control = get_control();
    if (control == 0) {
      console.error(error);
      alert("Velg en post!");
      return 0;
    }
    let formData = new FormData();
    formData.append(name= 'password', value=document.getElementById('password').value)
    formData.append(name= 'control', value=control);
    formData.append(name= 'id', value=id);
    time = new Date(Date.now()).toISOString().split('.')[0]+"Z"
    formData.append('time', time);
    response = fetch("passing.php", {
        method: "POST",
        body: formData,
    })
    .then(response => {
      if (response.status == 401) {
      alert("Feil passord!")
    }
    })

    update()
    document.getElementById("search").focus()
};
function filterTable() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("search");
  filter = input.value;
  table = document.getElementById("runners").getElementsByTagName("tbody")[0];
  if (!table) {
    setTimeout(filterTable, 100);
  }
  tr = table.getElementsByTagName("tr");

  if (filter == "") {
    for (i = 0; i < tr.length; i++) {
      tr[i].style.display = "";
    }
  }
  else {
  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td");
    txtValue = td[0].textContent;
    if (txtValue == filter) {
      tr[i].style.display = "";
    } else {
      tr[i].style.display = "none";
    }
  }
}
}

function update() {
        const table = document.getElementById("runners");
        control = get_control();
        let request = new Request(`table.php?registrering,`+control);
        fetch(request)
        .then((response) => response.text())
        .then((text) => {table.innerHTML = text;})
        filterTable()
    }
</script>
</body>
</html>