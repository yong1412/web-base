
$(document).ready(function() { // Form validation function - Checks required fields and email format before submission.

    $('#registration-form').on('submit', function(e) {
        var isValid = true;
        var errors = [];

        $(this).find('input[required], textarea[required]').each(function() {
            if ($(this).val().trim() === '') {
                errors.push($(this).attr('name') + ' is required.');
                isValid = false;
            }
        });

        var email = $(this).find('input[name="email"]').val();
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            errors.push('Invalid email format.');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            Swal.fire({
                title: 'Validation Error',
                html: errors.join('<br>'),
                icon: 'error'
            });
        }
    });

    $('input[type="file"]').on('change', function() { // File validation - Ensures only valid image files are selected.
        var file = this.files[0];
        if (file) {
            var allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (allowedTypes.indexOf(file.type) === -1) {
                Swal.fire({
                    title: 'Invalid File Type',
                    text: 'Please select a valid image file (JPEG, PNG, GIF).',
                    icon: 'error'
                });
                $(this).val('');
            }
        }
    });
});