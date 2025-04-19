function makeEditable(element) {
    var content = element.innerHTML;
    element.innerHTML = '<input type="text" value="' + content + '" />';
    element.firstChild.focus();
}

function saveChanges(rowId, columnName, newValue) {
    // Here you would send an AJAX request to a PHP script to update the database
    // For the purpose of this example, we're just logging to the console
    console.log("Saving changes to rowId: " + rowId + ", columnName: " + columnName + ", newValue: " + newValue);
    // Implement the actual save logic here
}

window.onload = function () {
    var table = document.getElementById("icpTable");
    for (var i = 1; i < table.rows.length; i++) {
        var row = table.rows[i];
        for (var j = 0; j < row.cells.length - 1; j++) {
            row.cells[j].ondblclick = function () {
                makeEditable(this);
            };
        }
    }
};

function saveRecordChanges(recordId, columnName, newValue) {
    $.post('change_adm.php', {
        action: 'save',
        id: recordId,
        columnName: columnName,
        newValue: newValue
    }, function (response) {
        // Handle response
        console.log(response);
    });
}

function passRecordC(recordICP) {
    $.post('change_adm.php', {action: 'pass', icp_number: recordICP}, function (response) {
        console.log(response);
        location.reload(); // Reload the page to reflect changes
    });
}

function rejectRecordC(recordId) {
    $.post('change_adm.php', {action: 'reject', id: recordId}, function (response) {
        console.log(response);
        location.reload(); // Reload the page to reflect changes
    });
}

function deleteRecordC(recordId) {
    $.post('change_adm.php', {action: 'delete', id: recordId}, function (response) {
        console.log(response);
        location.reload(); // Reload the page to reflect changes
    });
}

window.onload = function () {
    var editableElements = document.querySelectorAll('.editable');
    editableElements.forEach(function (elem) {
        elem.addEventListener('focusout', function (e) {
            var recordId = elem.getAttribute('data-id');
            var columnName = elem.getAttribute('data-column');
            var newValue = e.target.value;
            saveRecordChanges(recordId, columnName, newValue);
            elem.innerText = newValue;
        });
    });
};