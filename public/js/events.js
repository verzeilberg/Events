$(document).ready(function () {
    $(document).on('change', ':file', function () {
        var input = $(this),
                numFiles = input.get(0).files ? input.get(0).files.length : 1,
                label = input.val().replace(/\\/g, '/').replace(/.*\//, '');
        input.trigger('fileselect', [numFiles, label]);

        $('label.custom-file-label').html(label);
    });


    $("input[name=eventStartDate]").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        autoclose: true,
        todayBtn: true,
        startDate: "2013-02-14 10:00",
        minuteStep: 1
    });
    $("input[name=eventEndDate]").datetimepicker({
        format: 'yyyy-mm-dd hh:ii',
        autoclose: true,
        todayBtn: true,
        startDate: "2013-02-14 10:00",
        minuteStep: 1
    });

    // Replace the <textarea id="editor1"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace('editor1');
    CKEDITOR.replace('editor2');

});