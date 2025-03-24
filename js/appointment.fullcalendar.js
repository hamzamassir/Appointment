(function ($, Drupal) {
    Drupal.behaviors.appointmentFullCalendar = {
      attach: function (context, settings) {
        // Ensure we only initialize once.
        $('#fullcalendar-container', context).once('fullcalendar').each(function () {
          // Initialize FullCalendar.
          var calendarEl = document.getElementById('fullcalendar-container');
          var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            selectable: true,
            headerToolbar: {
              left: 'prev,next today',
              center: 'title',
              right: 'dayGridMonth,timeGridWeek,listMonth'
            },
            // When a date is selected, store it in a hidden field.
            dateClick: function(info) {
              // Set the hidden field value.
              $('#edit-selected-date').val(info.dateStr);
              // Optionally highlight the selected cell.
              // You may also add additional logic to initialize available time slots.
            },
            // You can add more options here as needed.
          });
          calendar.render();
        });
      }
    };
  })(jQuery, Drupal);