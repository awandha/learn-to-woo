jQuery(document).ready(function($) {
    const $field = $('#engraving_text');
    const $counter = $('.engraving-counter');
    const $preview = $('#engraving-preview-text');
    const maxLength = parseInt($field.attr('maxlength'), 10) || 30;

    function updateUI() {
        const val = $field.val();
        const length = val.length;

        // Update counter
        $counter.text(length + '/' + maxLength + ' characters');

        // Toggle warning styles + animation
        if (length >= maxLength) {
            $counter.css('color', 'red');
            $field.css('border-color', 'red');

            // Add shake effect when user tries to type more
            if (length === maxLength) {
                $field.addClass('shake');
                setTimeout(() => $field.removeClass('shake'), 300);
            }
        } else {
            $counter.css('color', '#666'); // normal gray
            $field.css('border-color', '');
        }

        // Update preview
        $preview.text(length ? val : '-');
    }

    $field.on('input', updateUI);
    updateUI(); // initialize
});
