$(document).ready(function() {
    $('.copy-button').on('click', function() {
        const factText = $(this).data('fact');
        const notification = $('#clipboard-notification');

        navigator.clipboard.writeText(factText).then(() => {
            notification.fadeIn(300).delay(1000).fadeOut(300);
        }).catch(err => {
            console.error('Failed to copy text: ', err);
            alert('Failed to copy text!'); // Fallback alert in case of error
        });
    });
});