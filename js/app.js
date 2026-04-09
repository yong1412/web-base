
$(document).ready(function() { //only run after the webpage is fully loaded.

    $('.delete-btn').on('click', function(e) {  //click on any button with class delete-btn.
        e.preventDefault(); //prevents the link from immediately opening (so confirmation can appear first).
        var url = $(this).attr('href');  //gets the link (URL) from the clicked button.
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });

    // Block/Unblock confirmation
    $('.block-btn, .unblock-btn').on('click', function(e) {
        e.preventDefault();
        var url = $(this).attr('href');
        var action = $(this).hasClass('block-btn') ? 'block' : 'unblock';
        Swal.fire({
            title: 'Confirm ' + action,
            text: 'Are you sure you want to ' + action + ' this member?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, ' + action + ' it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    });
});