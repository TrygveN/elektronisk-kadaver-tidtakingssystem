<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="matcha.css">
    <style>
        .passed {
            background-color: #8ff0a4;
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

<button onmousedown="update()">Oppdater</button>
<input id="search" type="number" class="form-control"  onkeyup="filterTable()"  placeholder="Søk">
<br>

<table>
    <tbody id="runners">

    </tbody>
</table>
</body>
<?php
$action = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
print_r($action)
?>
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
    formData.append(name= 'control', value=control);
    formData.append(name= 'id', value=id);
    time = new Date(Date.now()).toISOString().split('.')[0]+"Z"
    formData.append('time', time);
    fetch("upload.php", {
        method: "POST",
        body: formData,
    });
    update()
    //document.getElementById("search").focus()
};
function create_rows(csv) {
    csv = csv.split('\n')
    rows = "<tr><th>#</th><th>Navn</th><th></th>";
    for (i in csv) {
        data = csv[i].split(",")
        rows += create_row(data[0], data[1], false)
    }
    return rows
}
function filterTable() {
  var input, filter, table, tr, td, i, txtValue;
  input = document.getElementById("search");
  filter = input.value.toUpperCase();
  table = document.getElementById("runners");
  tr = table.getElementsByTagName("tr");

  for (i = 0; i < tr.length; i++) {
    td = tr[i].getElementsByTagName("td");
    for (var j = 0; j < td.length; j++) {
      txtValue = td[j].textContent || td[j].innerText;
      if (txtValue.toUpperCase().indexOf(filter) > -1) {
        tr[i].style.display = "";
        break;
      } else {
        tr[i].style.display = "none";
      }
    }
  }
}
function update_row(id, name, passed) {
    row = document.getElementById(id)
    row.innerHTML = create_row(id, name, passed)
    row.classList.add("bg-success");
}

function update() {
        const table = document.getElementById("runners");
        control = get_control();
        const myRequest = new Request(`get_table.php?registrering,`+control);
        fetch(myRequest)
        .then((response) => response.text())
        .then((text) => {
        table.innerHTML = text;
        });
    }

update()
</script>
<html>