<!DOCTYPE html>
<html lang="no">
<head>
    <style>
        .passed {
            background-color: #8ff0a4;
        }
    </style>
</head>
<body>
<table>
    <tbody id="runners">

    </tbody>
</table>
</body>
<script>
// Hvilken matpost vi er på:
var control = 1;

function register_runner(id) {
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
    rows = ""
    for (i in csv) {
        data = csv[i].split(",")
        rows += create_row(data[0], data[1], false)
    }
    return rows
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