(function ($, Drupal, once) {
    'use strict';
  
    Drupal.behaviors.appointmentFullCalendar = {
      attach: function (context, settings) {
        once('fullcalendar', '#fullcalendar-container', context).forEach(function (el) {
          // Debug settings
          console.log('Appointment Settings:', settings.appointment);
          
          var selectedDate = (settings.appointment && settings.appointment.selectedDate) 
            ? settings.appointment.selectedDate 
            : new Date();
  
          var availableSlots = (settings.appointment && settings.appointment.availableSlots) 
            ? settings.appointment.availableSlots 
            : [];
          
          var operatingDays = (settings.appointment && settings.appointment.operatingDays) 
            ? settings.appointment.operatingDays 
            : [];
  
          // Helper function to get day name in lowercase
          function getDayName(date) {
            return date.toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase();
          }
  
          var calendar = new FullCalendar.Calendar(el, {
            initialView: 'timeGridDay',
            initialDate: selectedDate,
            slotMinTime: '08:00:00',
            slotMaxTime: '19:00:00',
            slotDuration: '01:00:00',
            allDaySlot: false,
            selectable: true,
            selectConstraint: 'availableSlots',
            headerToolbar: {
              left: '',
              center: 'title',
              right: ''
            },
            // Validate day selection
            dayCellDidMount: function(arg) {
              const dayName = getDayName(arg.date);
              if (!operatingDays.includes(dayName)) {
                arg.el.classList.add('fc-day-disabled');
              }
            },
            // Prevent navigation to non-operating days
            dateClick: function(info) {
              const clickedDayName = getDayName(info.date);
              if (!operatingDays.includes(clickedDayName)) {
                alert('This day is not an operating day. Please select another day.');
                return false;
              }
            },
            eventClick: function(info) {
              if (info.event.extendedProps.isAvailable) {
                // Clear previous selections
                calendar.getEvents().forEach(function(evt) {
                  if (evt.extendedProps.isAvailable) {
                    evt.setProp('backgroundColor', '#28a745');
                    evt.setProp('borderColor', '#1e7e34');
                  }
                });
  
                // Highlight selected slot
                info.event.setProp('backgroundColor', '#007bff');
                info.event.setProp('borderColor', '#0056b3');
  
                // Get the time slot
                var timeSlot = info.event.extendedProps.timeSlot;
                
                // Set both hidden inputs
                document.getElementById('edit-time').value = timeSlot;
                document.getElementById('edit-selected-time').value = timeSlot;
  
                // Update display
                var formattedTime = new Date('2000-01-01T' + timeSlot).toLocaleTimeString([], {
                  hour: 'numeric',
                  minute: '2-digit'
                });
                document.getElementById('time-display').textContent = formattedTime;
  
                console.log('Selected time slot:', {
                  raw: timeSlot,
                  formatted: formattedTime,
                  date: info.event.start.toISOString().split('T')[0]
                });
              }
            },
            events: function(info, successCallback, failureCallback) {
              try {
                const currentDayName = getDayName(info.start);
                
                // Check if current day is an operating day
                if (!operatingDays.includes(currentDayName)) {
                  successCallback([]); // No events for non-operating days
                  return;
                }
  
                var events = [];
                
                // Only process available slots
                availableSlots.forEach(function(slot) {
                  var timeSlot = slot.time;
                  // Normalize time format
                  if (timeSlot.length === 4) {
                    timeSlot = timeSlot.substr(0, 2) + ':' + timeSlot.substr(2, 2);
                  }
  
                  // Check if this slot is available
                  var slotKey = timeSlot.replace(':', '');
                  if (!settings.appointment.bookedSlots.includes(slotKey)) {
                    var [hours, minutes] = timeSlot.split(':');
                    var startDate = new Date(info.start);
                    startDate.setHours(parseInt(hours), parseInt(minutes), 0);
                    
                    var endDate = new Date(startDate);
                    endDate.setHours(startDate.getHours() + 1);
  
                    events.push({
                      start: startDate,
                      end: endDate,
                      title: slot.formatted + ' - Available',
                      backgroundColor: '#28a745',
                      borderColor: '#1e7e34',
                      textColor: '#ffffff',
                      extendedProps: {
                        timeSlot: timeSlot,
                        isAvailable: true
                      },
                      interactive: true,
                      selectable: true
                    });
                  }
                });
  
                console.log('Generated events:', events);
                successCallback(events);
              } catch (error) {
                console.error('Error generating events:', error);
                failureCallback(error);
              }
            }
          });
  
          // Add custom CSS
          var style = document.createElement('style');
          style.textContent = `
            .fc-day-disabled {
              background-color: #f8f9fa;
              cursor: not-allowed;
            }
            .fc-event {
              cursor: pointer;
            }
          `;
          document.head.appendChild(style);
  
          calendar.render();
        });
      }
    };
  })(jQuery, Drupal, once);