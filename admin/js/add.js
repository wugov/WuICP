function addRecord(recordICP) {
    $.post('add_adm.php', {action: 'add', icp_number: recordICP}, function (response) {
        console.log(response);
        location.reload(); // Reload the page to reflect changes
    });
}