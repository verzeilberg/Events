/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$(document).ready(function () {
    $("button.eventDetails, img.eventDetails").on("click", function () {
        var eventId = $(this).data('eventid');
        $.ajax({
            type: 'POST',
            data: {
                eventId: eventId
            },
            url: "/events/event",
            async: true,
            success: function (data) {
                if (data.succes === true) {
                    $('h5#eventTitle').text(data.event.title);
                    $('div#startDate').html('<i class="fas fa-calendar-alt"></i> ' + data.event.eventStartDate);
                    $('div#endDate').html('<i class="far fa-calendar-alt"></i> ' + data.event.eventEndDate);
                    $('div#mainText').html(data.event.text);
                    $('div#eventCategory').html('<img src="'+data.event.categoryImage+'" alt="'+data.event.category+'" height="80" />');
                    $('div#eventBackground').css('background-image', 'url(' + data.event.eventImage + ')');
                    $('#eventModal').modal('toggle')
                } else {
                    alert(data.errorMessage);
                }
            }
        });
    });
    $(".closeEvent").on("click", function () {
        $('#eventModal').modal('hide');
    });
    
});
