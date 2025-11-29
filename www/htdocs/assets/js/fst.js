// Functions of the dbadmin/fst.php script

document.addEventListener("DOMContentLoaded", function () {
    var selects = document.querySelectorAll("select[name='row_tech']");
    selects.forEach(function (sel) {
        var val = sel.getAttribute("data-selected");
        if (val) sel.value = val;
    });
});

function insertHelperRow() {
    var id = document.getElementById('new_id').value.trim();
    var tech = document.getElementById('new_tech').value;
    var prefix = document.getElementById('new_prefix').value.trim();
    var field = document.getElementById('new_field').value.trim();

    if (id === "" || field === "") {
        alert(erridfield);
        return;
    }

    var fmt = "";
    switch (tech) {
        case "0":
            fmt = prefix !== "" ? `mpu,(|${prefix}|${field}|%|/)/` : `mpu,(${field}|%|/)/`;
            break;
        case "8":
            fmt = prefix !== "" ? `mpu,'/${prefix}/' (${field}|%|/),` : `mpu,(${field}|%|/),`;
            break;
        case "5":
            fmt = prefix !== "" ? `'${prefix}', mpu, (${field}/)` : `mpu, (${field}/)`;
            break;
        case "1":
        default:
            fmt = `mpu, ${field}`;
            if (prefix) fmt = `'${prefix}' ` + fmt;
            break;
    }

    addRow(id, tech, fmt);
    document.getElementById('new_prefix').value = "";
    document.getElementById('new_field').value = "";
}

function addEmptyRow() {
    addRow("", "0", "");
}

function addRow(id, tech, fmt) {
    var tbody = document.getElementById("fstBody");
    var tr = document.createElement("tr");
    tr.className = "fst-row";

    tr.innerHTML = `
            <td><input type="text" name="row_id" value="${id}"></td>
            <td><select name="row_tech">${techSelectOptions}</select></td>
            <td><textarea name="row_fmt" rows="1">${fmt}</textarea></td>
            <td class="actions-cell">
                <button type="button" class="bt bt-gray" onclick="moveRow(this, -1)" title="Subir"><i class="fas fa-arrow-up"></i></button>
                <button type="button" class="bt bt-gray" onclick="moveRow(this, 1)" title="Descer"><i class="fas fa-arrow-down"></i></button>
                <button type="button" class="bt bt-blue" onclick="duplicateRow(this)" title="Duplicar"><i class="far fa-copy"></i></button>
                <button type="button" class="bt bt-red" onclick="deleteRow(this)" title="Excluir"><i class="fas fa-trash-alt"></i></button>
            </td>
        `;

    tbody.appendChild(tr);
    var select = tr.querySelector("select[name='row_tech']");
    if (select) select.value = tech;
}

function deleteRow(btn) {
    if (confirm(are_you_sure)) {
        var row = btn.closest("tr");
        row.remove();
    }
}

function duplicateRow(btn) {
    var row = btn.closest("tr");
    var clone = row.cloneNode(true);
    var origId = row.querySelector("input[name='row_id']").value;
    var origTech = row.querySelector("select[name='row_tech']").value;
    var origFmt = row.querySelector("textarea[name='row_fmt']").value;

    clone.querySelector("input[name='row_id']").value = origId;
    clone.querySelector("textarea[name='row_fmt']").value = origFmt;

    var cloneSelect = clone.querySelector("select[name='row_tech']");
    cloneSelect.innerHTML = techSelectOptions;
    cloneSelect.value = origTech;

    row.parentNode.insertBefore(clone, row.nextSibling);
}

function moveRow(btn, direction) {
    var row = btn.closest("tr");
    var tbody = row.parentNode;
    if (direction === -1 && row.previousElementSibling) {
        tbody.insertBefore(row, row.previousElementSibling);
    } else if (direction === 1 && row.nextElementSibling) {
        tbody.insertBefore(row.nextElementSibling, row);
    }
}

function coletarDados() {
    var rows = document.querySelectorAll(".fst-row");
    var data = [];
    rows.forEach(function (row) {
        var id = row.querySelector("input[name='row_id']").value.trim();
        var tech = row.querySelector("select[name='row_tech']").value;
        var fmt = row.querySelector("textarea[name='row_fmt']").value;
        fmt = fmt.replace(/(\r\n|\n|\r)/gm, " ");

        if (id !== "") {
            data.push(id + " " + tech + " " + fmt);
        }
    });
    return data.join("\n");
}

function Enviar() {
    var content = coletarDados();
    document.forma1.ValorCapturado.value = content;
    document.forma1.submit();
}

function Test() {
    var mfn = document.getElementById("MfnTest").value.trim();
    if (mfn === "") {
        alert("<?php echo $msgstr['mismfn'] ?? 'Indique um MFN'; ?>");
        return;
    }
    document.test.Mfn.value = mfn;
    document.test.ValorCapturado.value = coletarDados();
    var msgwin = window.open("", "FST_Test", "width=800,height=600,scrollbars=yes,resizable=yes");
    msgwin.focus();
    document.test.submit();
}