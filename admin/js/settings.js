function sendRequest() {
    $.post('settings.php', {action: 'test', data: 0}, function (response) {
        console.log(response);
        location.reload(); // Reload the page to reflect changes
    });
}