/* 
 *
 * Load everything when document is ready
 */
$(document).ready(function () {

    /*
     * On click show detail modal of a event
     */
    $("#eventItems").on("click", '.eventDetails', function () {



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
                    $('div#eventCategory').html('<img src="' + data.event.categoryImage + '" alt="' + data.event.category + '" height="80" />');
                    $('div#eventBackground').css('background-image', 'url(' + data.event.eventImage + ')');
                    $('#eventModal').modal('toggle');
                } else {
                    alert(data.errorMessage);
                }
            }
        });
    });


    /*
     * Close event detail modal
     */
    $(".closeEvent").on("click", function () {
        $('#eventModal').modal('hide');
    });


    /*
     * Create events on change of one of the 2 select fields
     */
    $("select#year, select#category").on("change", function () {
        createEvents();
    });


});


/*
 * Create variables for google maps
 */
var map = null;
var infowindow = null;
var markers = [];

/*
 * Initiate the google maps with markers
 */
function initMap() {
    //Set the map variable with google maps
    map = new google.maps.Map(document.getElementById('map'), {
        center: new google.maps.LatLng(52.1979222, 5.0211668),
        zoom: 7.76
    });

    //Declare infowindow
    infowindow = new google.maps.InfoWindow();

    //Create events on google mpas and on page
    createEvents();

}


/*
 * Set markers with info windows on google maps
 */
function setMarkers(infowindow, map, locations) {
    //declare marker call it 'i'
    var marker, i;
    //add marker to each locations
    for (i = 0; i < locations.length; i++) {
        marker = new google.maps.Marker({
            position: new google.maps.LatLng(locations[i][1], locations[i][2]),
            map: map,
            icon: new google.maps.MarkerImage(
                locations[i][3],
                null,
                null,
                null,
                new google.maps.Size(40, 55)
            )
        });
        markers.push(marker);
        //click function to marker, pops up infowindow
        google.maps.event.addListener(marker, 'click', (function (marker, i) {
            return function () {
                infowindow.setContent(locations[i][0]);
                infowindow.open(map, marker);
            }
        })(marker, i));
    }
}

/*
 * Set markers on map
 */
function setMapOnAll(map) {
    for (var i = 0; i < markers.length; i++) {
        markers[i].setMap(map);
    }
}

/*
 * Clear markers on map
 */
function clearMarkers() {
    setMapOnAll(null);
}


/*
 * Create events on google maps and events on page
 */
function createEvents() {
    var year = $('select#year').val();
    var category = $('select#category').val();

    $.ajax({
        url: "/events/get-locations",
        dataType: 'json',
        data: {
            year: year,
            category: category
        },
        async: true,
        type: 'post',
        success: function (response) {

            if (response.success == true) {
                //We remove the old markers
                clearMarkers();
                //Adding them to the map
                setMarkers(infowindow, map, response.locations);

                drawEvents(response.events);


            } else {
                alert(response.errorMessage);
            }
        }
    });
}


/*
 * Draw events on maps and page
 */
function drawEvents(events) {
    $("div.eventItem").remove();

    $.each(events, function (index, event) {
        var image = _.filter(event.eventImage.imageTypes, {'imageTypeName': '400x200'})[0];
        var eventHtml = '<div id="eventItem' + event.id + '" class="col-sm-12 col-md-6 mb-4 eventItem">';
        eventHtml += '<div class="item">';
        if (moment(event.eventEndDate.date) < moment()) {
            eventHtml += '<img class="finishedStamp img-responsive" src="/img/finished.png" alt="finished" />';
        }
        eventHtml += '<img class="img-responsive eventDetails" data-eventid="' + event.id + '" style="width:100%;" src="' + image.folder + '' + image.fileName + '" alt=""><div class="card-body">';
        eventHtml += '<h4 class="mt-4">' + event.title + '</h4>';
        eventHtml += '<div class="row">';
        eventHtml += '<div class="col-sm-12 col-md-6">';
        eventHtml += '<p class="card-text">';
        eventHtml += ' <i class="fas fa-calendar-alt"></i>&nbsp;' + moment(event.eventStartDate.date).format('DD-MM-YYYY') + '</p>';
        eventHtml += '</div>';
        eventHtml += '<div class="col-sm-12 col-md-6">';
        eventHtml += '<p class="card-text text-end">';
        eventHtml += '<i class="far fa-calendar-alt"></i>&nbsp;' + moment(event.eventEndDate.date).format('DD-MM-YYYY') + '</p>';
        eventHtml += '</div>';
        eventHtml += '</div>';
        eventHtml += '<button class="btn btn-dark col text-right eventDetails" data-eventid="' + event.id + '"><i class="fas fa-angle-double-right"></i><i class="fas fa-angle-double-right"></i><i class="fas fa-angle-double-right"></i>';
        eventHtml += '</button>';
        eventHtml += '</div>';
        eventHtml += '</div>';
        eventHtml += '</div>';

        $('#eventItems').append(eventHtml);
    });
}
