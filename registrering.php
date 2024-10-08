<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8" />
    <title>EKT</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <meta name="description" content="Elektronisk Kadaver Tidtakningssystem" />
    <link rel="stylesheet" href="https://matcha.mizu.sh/matcha.css">
    <style>
        .passed {
            background-color: #8ff0a4;
        }
        fieldset {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-evenly;
        }
    </style>
</head>
<body>
<fieldset>
    <legend>Velg post</legend>
    <label>
      <input type="radio" name="post" value="1">
      1. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="2">
      2. Matpost
    </label>
    <label>
      <input type="radio" name="post" value="3">
      Mål
    </label>
  </fieldset>


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

function register_runner(id) {
    control = document.querySelector('input[name="post"]:checked').value;
    let formData = new FormData();
    formData.append(name= 'control', value=control);
    formData.append(name= 'id', value=id);
    time = new Date(Date.now()).toISOString().split('.')[0]+"Z"
    formData.append('time', time);
    fetch("upload.php", {
        method: "POST",
        body: formData,
    });
};
function update_runner_status(id, name) {
    update_row(id, name, true);
    register_runner(id);
}

function read_db() {
    let xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", "db.csv", false);
    xmlHttp.send(null);
    return xmlHttp.responseText;
}

function create_row(id, name, passed) {
    if (!passed) {
        button = `<button onclick="update_runner_status(${id}, '${name}')">✓</button>`
        return `<tr id="${id}"><td>${id}</td><td>${name}</td><td>${button}</td></tr>`
    }
    else {
        button = `<button onclick="update_runner_status(${id}, '${name}')">Angre</button>`
        return `<tr id="${id}" class="passed"><td>${id}</td><td>${name}</td><td>${button}</td></tr>`
    }

}

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
    row.classList.add("passed");
}
table = document.getElementById("runners")
table.innerHTML = create_rows(read_db())
</script>
<html>