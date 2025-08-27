jQuery(document).ready(function($) {
    const $field = $('#engraving_text');
    const $counter = $('.engraving-counter');
    const $preview = $('#engraving-preview-text');
    const maxLength = parseInt($field.attr('maxlength'), 10) || 30;

    function updateUI() {
        const val = $field.val();
        $counter.text(val.length + '/' + maxLength + ' characters');
        $preview.text(val.length ? val : '-');
    }

    $field.on('input', updateUI);
    updateUI(); // initialize
});
