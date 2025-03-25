<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a multi-step form for booking appointments.
 */
class AppointmentMultiStepForm extends FormBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new AppointmentMultiStepForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'appointment_multistep_form';
    }

    /**
     *
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $step = $form_state->get('step') ?: 1;
        $form_state->set('step', $step);
        switch ($step) {
            case 1:
                return $this->buildStepOne($form, $form_state);

            case 2:
                return $this->buildStepTwo($form, $form_state);

            case 3:
                return $this->buildStepThree($form, $form_state);

            case 4:
                return $this->buildStepFour($form, $form_state);

            case 5:
                return $this->buildStepFive($form, $form_state);
        }
    }

  /**
   * Helper: Render a step title.
   *
   * @param string $title
   *   The title text.
   *
   * @return array
   *   Render array for the title.
   */
    protected function buildStepTitle($title)
    {
        return [
        '#type' => 'markup',
        '#markup' => '<h2>' . $this->t($title) . '</h2>',
        ];
    }

  /**
   * Helper: Build actions buttons.
   *
   * @param array $buttons
   *   An associative array of buttons (keys like 'back', 'next', 'submit').
   *
   * @return array
   *   The render array for the actions.
   */
    protected function buildActions(array $buttons = [])
    {
        $actions = ['#type' => 'actions'];
        foreach ($buttons as $key => $button) {
            $actions[$key] = $button;
        }
        return $actions;
    }

    /**
     * Summary of buildStepOne
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    protected function buildStepOne(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 1: Select Agency');

        $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
        $options = [];
        foreach ($agencies as $agency) {
            $options[$agency->id()] = $agency->label();
        }

        $form['agency'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Agency'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select an agency -'),
        ];

        $form['actions'] = $this->buildActions([
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepOne'],
        ],
        ]);

        return $form;
    }

    /**
     * Summary of buildStepTwo
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    protected function buildStepTwo(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 2: Select Specialization');

        $specializations = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'specializations']);
        $options = [];
        foreach ($specializations as $term) {
            $options[$term->id()] = $term->label();
        }
        $form['specialization'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Specialization'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select specialization -'),
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepTwo'],
        ],
        ]);

        return $form;
    }

    /**
     * Summary of buildStepThree
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    protected function buildStepThree(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 3: Select Adviser');

        $agency_id = $form_state->get('agency');
        $specialization_id = $form_state->get('specialization');

      // Load advisers matching agency and specialization.
        $query = $this->entityTypeManager->getStorage('user')->getQuery()
        ->condition('status', 1)
        ->condition('roles', 'adviser')
        ->condition('field_agency', $agency_id)
        ->condition('field_specializations', $specialization_id)
        ->accessCheck(true);
        $adviser_ids = $query->execute();

        $advisers = $this->entityTypeManager->getStorage('user')->loadMultiple($adviser_ids);
        $options = [];
        foreach ($advisers as $adviser) {
            $options[$adviser->id()] = $adviser->getDisplayName();
        }

        $form['adviser'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Adviser'),
        '#options' => $options,
        '#required' => true,
        '#empty_option' => $this->t('- Select adviser -'),
        ];

      // Date and time fields (simplified for this step; details refined in step 4).
        $form['date'] = [
        '#type' => 'date',
        '#title' => $this->t('Appointment Date'),
        '#required' => true,
        ];
        $form['time'] = [
        '#type' => 'time',
        '#title' => $this->t('Appointment Time'),
        '#required' => true,
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepThree'],
        ],
        ]);

        return $form;
    }

    /**
     * Summary of buildStepFour
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    protected function buildStepFour(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 4: Select Date and Time');

      // Get the selected date from the previous step
        $selected_date = $form_state->get('date') ?: date('Y-m-d');

      // Debug log
        \Drupal::logger('appointment')->notice('Building Step 4 with date: @date', [
          '@date' => $selected_date
        ]);

      // Load adviser and agency
        $adviser_id = $form_state->get('adviser');
        $agency_id = $form_state->get('agency');
        $adviser = $this->entityTypeManager->getStorage('user')->load($adviser_id);
        $agency = $this->entityTypeManager->getStorage('agency')->load($agency_id);

      // Get operating days from agency
        $operating_days_values = $agency->get('operating_days')->getValue();
        $operating_days = [];
        if (!empty($operating_days_values)) {
            foreach ($operating_days_values as $day_entry) {
                $operating_days[] = strtolower(trim($day_entry['value']));
            }
        }

      // Check if selected date is an operating day
        $selected_day = strtolower(date('l', strtotime($selected_date)));
        if (!in_array($selected_day, $operating_days)) {
            \Drupal::logger('appointment')->notice('Selected day (@day) is not an operating day. Operating days: @operating_days', [
              '@day' => $selected_day,
              '@operating_days' => implode(', ', $operating_days)
            ]);
            $this->messenger()->addWarning($this->t('The selected day (@day) is not an operating day for this agency. Please select another day.', [
              '@day' => ucfirst($selected_day)
            ]));
            $form_state->set('step', 3);
                $form_state->setRebuild(true);
                return $this->buildStepThree($form, $form_state);
        }

      // Get working hours
        $working_hours = [];
        if ($adviser->hasField('field_working_hours') && !$adviser->get('field_working_hours')->isEmpty()) {
            foreach ($adviser->get('field_working_hours')->getValue() as $hour) {
                $time = $hour['value'];
                // Ensure proper time format
                if (strlen($time) === 4) {
                    $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
                }
                $working_hours[] = [
                  'time' => $time,
                  'formatted' => date('g:i A', strtotime($time))
                ];
            }
        }

        // Query for appointments for this adviser.
        $query = $this->entityTypeManager->getStorage('appointment')->getQuery()
        ->condition('adviser', $adviser_id)
        ->accessCheck(false);
        $appointment_ids = $query->execute();

        $booked_slots = [];
        if (!empty($appointment_ids)) {
            $appointments = $this->entityTypeManager->getStorage('appointment')->loadMultiple($appointment_ids);
            foreach ($appointments as $appointment) {
              // Extract the date part (first 10 characters) from appointment_date.
                $appt_date = substr($appointment->get('appointment_date')->value, 0, 10);
                if ($appt_date === $selected_date) {
                    // Retrieve the booked time slot (e.g., "0900").
                    $booked_slots[] = $appointment->get('time_slot')->value;
                }
            }
        }
        \Drupal::logger('appointment')->debug('Booked slots for @date: @slots', [
        '@date' => $selected_date,
        '@slots' => implode(', ', $booked_slots),
        ]);

      // Calculate available slots
        $available_slots = array_filter($working_hours, function ($slot) use ($booked_slots) {
            $slot_value = str_replace(':', '', $slot['time']);
            return !in_array($slot_value, $booked_slots);
        });

      // Debug log available slots
        \Drupal::logger('appointment')->notice('Available
        slots: @slots', [
          '@slots' => print_r(array_values($available_slots), true)
        ]);
        \Drupal::logger('appointment')->notice('booked slots: @slots', [
          '@slots' => print_r($booked_slots, true)
        ]);\Drupal::logger('appointment')->notice('appointmments id : @slots', [
          '@slots' => print_r($appointment_ids, true)
        ]);

      // Attach the FullCalendar library
        $form['#attached']['library'][] = 'appointment/fullcalendar';

      // Pass data to JavaScript
        $form['#attached']['drupalSettings']['appointment'] = [
          'availableSlots' => array_values($available_slots),
          'selectedDate' => $selected_date,
          'operatingDays' => array_map('strtolower', $operating_days), // Make sure they're lowercase
          'bookedSlots' => $booked_slots,
        ];

      // Calendar container
        $form['calendar_wrapper'] = [
          '#type' => 'container',
          '#attributes' => [
              'class' => ['calendar-wrapper'],
          ],
        ];

        $form['calendar_wrapper']['calendar'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#attributes' => [
              'id' => 'fullcalendar-container',
              'style' => 'height: 600px; margin-bottom: 20px;',
          ],
        ];

      // Hidden fields
        $form['selected_date'] = [
          '#type' => 'hidden',
          '#default_value' => $selected_date,
          '#attributes' => ['id' => 'edit-selected-date'],
        ];

        $form['time'] = [
          '#type' => 'hidden',
          '#default_value' => '',
          '#attributes' => ['id' => 'edit-time'],
          '#required' => true,
        ];

      // Additional hidden field for time (backup)
        $form['selected_time'] = [
          '#type' => 'hidden',
          '#default_value' => '',
          '#attributes' => ['id' => 'edit-selected-time'],
        ];

      // Time display
        $form['selected_time_display'] = [
          '#type' => 'markup',
          '#prefix' => '<div id="selected-time-display" class="selected-time-wrapper">',
          '#suffix' => '</div>',
          '#markup' => $this->t('Selected time: <span id="time-display">None</span>'),
        ];

      // Add some helpful information
        $form['operating_days_info'] = [
          '#type' => 'markup',
          '#prefix' => '<div class="operating-days-info">',
          '#suffix' => '</div>',
          '#markup' => $this->t('Operating days: @days', [
              '@days' => implode(', ', array_map('ucfirst', $operating_days))
          ]),
        ];

      // Navigation buttons
        $form['actions'] = $this->buildActions([
          'back' => [
              '#type' => 'submit',
              '#value' => $this->t('Back'),
              '#submit' => ['::backToPrevious'],
              '#limit_validation_errors' => [],
          ],
          'next' => [
              '#type' => 'submit',
              '#value' => $this->t('Next'),
              '#submit' => ['::submitStepFour'],
              '#validate' => ['::validateStepFour'],
          ],
        ]);

      // Add some CSS for the calendar
        $form['#attached']['html_head'][] = [
          [
              '#type' => 'html_tag',
              '#tag' => 'style',
              '#value' => '
                  .calendar-wrapper { 
                      max-width: 900px; 
                      margin: 0 auto; 
                      padding: 20px;
                  }
                  .selected-time-wrapper {
                      text-align: center;
                      margin: 20px 0;
                      font-size: 1.1em;
                  }
                  .operating-days-info {
                      text-align: center;
                      margin: 10px 0;
                      color: #666;
                  }
                  .fc-day-disabled {
                      background-color: #f8f9fa;
                      cursor: not-allowed;
                  }
              ',
          ],
          'appointment_calendar_css'
        ];

        return $form;
    }

  /**
   * Validation handler for step four
   */
    public function validateStepFour(array &$form, FormStateInterface $form_state)
    {
        $time = $form_state->getValue('time');
        $selected_time = $form_state->getValue('selected_time');

        // Debug log
        \Drupal::logger('appointment')->notice('Validating Step 4 with time: @time, selected_time: @selected_time', [
          '@time' => $time,
          '@selected_time' => $selected_time
        ]);

        // Check both time fields
        if (empty($time) && empty($selected_time)) {
            $form_state->setErrorByName('time', $this->t('Please select an available time slot.'));
        }

        // If time is empty but selected_time exists, use that
        if (empty($time) && !empty($selected_time)) {
            $form_state->setValue('time', $selected_time);
        }
    }

    /**
     * Summary of buildStepFive
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return array
     */
    protected function buildStepFive(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 5: Personal Information');

        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Your Name'),
        '#required' => true,
        ];
        $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#required' => true,
        ];
        $form['phone'] = [
        '#type' => 'tel',
        '#title' => $this->t('Phone'),
        '#required' => true,
        ];
        $form['notes'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Notes'),
        ];

        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'],
        '#limit_validation_errors' => [],
        ],
        'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Book Appointment'),
        ],
        ]);

        return $form;
    }
    /**
     * Summary of submitStepOne
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return void
     */
    public function submitStepOne(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('agency', $form_state->getValue('agency'));
        $form_state->set('step', 2);
        $form_state->setRebuild(true);
    }

    /**
     * Summary of submitStepTwo
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return void
     */
    public function submitStepTwo(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('specialization', $form_state->getValue('specialization'));
        $form_state->set('step', 3);
        $form_state->setRebuild(true);
    }

    /**
     * Summary of submitStepThree
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return void
     */
    public function submitStepThree(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('adviser', $form_state->getValue('adviser'));
      // Also store date/time chosen in this step.
        $form_state->set('date', $form_state->getValue('date'));
        $form_state->set('time', $form_state->getValue('time'));
        $form_state->set('step', 4);
        $form_state->setRebuild(true);
    }

    /**
     * Summary of submitStepFour
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return void
     */
    public function submitStepFour(array &$form, FormStateInterface $form_state)
    {
      // Get time from either field
        $time = $form_state->getValue('time') ?: $form_state->getValue('selected_time');

      // Debug log
        \Drupal::logger('appointment')->notice('Submitting Step 4 with time: @time', [
          '@time' => $time
        ]);

        if (!empty($time)) {
            // Ensure time is in the correct format (HH:mm)
            if (strlen($time) === 4) {
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
            }
            $form_state->set('time', $time);
        }

      // Get the selected date
        $selected_date = $form_state->getValue('selected_date');
        if (!empty($selected_date)) {
            $form_state->set('date', $selected_date);
        }

        $form_state->set('step', 5);
        $form_state->setRebuild(true);
    }

    /**
     * Summary of backToPrevious
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @return void
     */
    public function backToPrevious(array &$form, FormStateInterface $form_state)
    {
        $current_step = $form_state->get('step');
        $form_state->set('step', $current_step - 1);
        $form_state->setRebuild(true);
    }

    /**
     * Summary of submitForm
     * @param array $form
     * @param \Drupal\Core\Form\FormStateInterface $form_state
     * @throws \Exception
     * @return void
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
          // Retrieve date value.
            $date = $form_state->getValue('date');
            if (empty($date)) {
                $date = $form_state->get('date');
            }
            if (empty($date)) {
                throw new \Exception('Date value is missing.');
            }

          // Retrieve time value.
            $time = $form_state->getValue('time');
            if (empty($time)) {
                $time = $form_state->get('time');
            }
            if (empty($time)) {
                throw new \Exception('Time value is missing.');
            }

          // Normalize time: If it's "0900", convert to "09:00".
            $time = (string) $time;
            if (strlen($time) == 4 && strpos($time, ':') === false) {
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
            }

          // Create a DateTime object.
            $date_time = \DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $time);
            if (!$date_time) {
                throw new \Exception('Could not parse date/time combination.');
            }
          // Format datetime in ISO8601 format: e.g., "2025-03-20T09:00:00"
            $formatted_datetime = $date_time->format('Y-m-d\TH:i:s');

          // Create the appointment entity.
            $appointment = $this->entityTypeManager->getStorage('appointment')->create([
            'agency'            => $form_state->get('agency'),
            'field_specializations'    => $form_state->get('specialization'),
            'adviser'           => $form_state->get('adviser'),
            'title'             => 'Appointment for ' . $form_state->getValue('name'),
            'customer_name'     => $form_state->getValue('name'),
            'customer_email'    => $form_state->getValue('email'),
            'customer_phone'    => $form_state->getValue('phone'),
            'appointment_date'  => $formatted_datetime,
            'time_slot'         => str_replace(':', '', $time),
            'notes'             => $form_state->getValue('notes'),
            'status'            => 'confirmed',
            ]);
            $appointment->save();
            $appointment_id = $appointment->id();
            if (!$appointment_id) {
                  throw new \Exception('Appointment entity saved without an ID.');
            }

          // Prepare mail parameters.
            $params = [
            'appointment_id'   => $appointment_id,
            'agency'           => $form_state->get('agency'),
            'adviser'          => $form_state->get('adviser'),
            'appointment_date' => $formatted_datetime,
            'time_slot'        => str_replace(':', '', $time),
            'customer_name'    => $form_state->getValue('name'),
            'customer_email'   => $form_state->getValue('email'),
            'customer_phone'   => $form_state->getValue('phone'),
            'notes'            => $form_state->getValue('notes'),
            ];
            $mail_manager = \Drupal::service('plugin.manager.mail');
            $module = 'appointment';
            $langcode = \Drupal::currentUser()->getPreferredLangcode();

          // Send email to the advisor.
            $advisor = $this->entityTypeManager->getStorage('user')->load($form_state->get('adviser'));
            $advisor_email = $advisor ? $advisor->getEmail() : '';
            if (!empty($advisor_email)) {
                $params['subject'] = 'New Appointment Booked';
                $result = $mail_manager->mail($module, 'new_appointment_advisor', $advisor_email, $langcode, $params, null, true);
                if ($result['result'] !== true) {
                    \Drupal::logger('appointment')->error('Failed to send email to advisor.');
                }
            }

          // Send email to the customer.
            $user_email = $form_state->getValue('email');
            if (!empty($user_email)) {
                $params['subject'] = 'Your Appointment is Confirmed';
                $result = $mail_manager->mail($module, 'new_appointment_user', $user_email, $langcode, $params, null, true);
                if ($result['result'] !== true) {
                    \Drupal::logger('appointment')->error('Failed to send email to customer.');
                }
            }

            $this->messenger()->addMessage($this->t('Your appointment has been booked successfully.'));
            // $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment_id]);
            $form_state->setRedirectUrl(Url::fromUri('internal:/appointment/modification'));
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('There was a problem booking your appointment. Please try again.'));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
