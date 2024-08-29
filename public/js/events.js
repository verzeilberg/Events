$(document).ready(function () {
    $(document).on('change', ':file', function () {
        var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);

        $('label.custom-file-label').html(label);
    });


    /** Init dateshift */
    $(".dateOnline, .dateOffline").dateshift({
        preappelement: '<i class="far fa-calendar-alt"></i>',
        preapp: 'app',
        nextButtonText: '<i class="far fa-caret-square-right"></i>',
        previousButtonText: '<i class="far fa-caret-square-left"></i>',
        dateFormat: 'yyyy-mm-dd'
    });

    /** Init timeshift */
    $("#timeOnline, #timeOffline").timeshift({
        hourClock: 24
    });

    /** Replace the <textarea id="editor1"> with a CKEditor
    instance, using default configuration. */
    CKEDITOR.replace('editor1');
    CKEDITOR.replace('editor2');

});
