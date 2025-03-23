<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a multi-step form for modifying appointments.
 *
 * This form adds two extra initial steps (phone entry and appointment selection)
 * and then follows the same flow as the original booking form so that the user
 * can modify agency, field_specializations, adviser/date/time, and personal information.
 */
class AppointmentModifyForm extends FormBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new AppointmentModifyMultiStepForm object.
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
        return 'appointment_modify_multistep_form';
    }

  /**
   * Build the form based on the current step.
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      // Determine the current step; default to step 1.
        $step = $form_state->get('step') ?: 1;
        $form_state->set('step', $step);

        switch ($step) {
            case 1:
                return $this->buildPhoneStep($form, $form_state);

            case 2:
                return $this->buildAppointmentSelection($form, $form_state);

            case 3:
                return $this->buildStepOne($form, $form_state);

            case 4:
                return $this->buildStepTwo($form, $form_state);

            case 5:
                return $this->buildStepThree($form, $form_state);

            case 6:
                return $this->buildStepFour($form, $form_state);
        }
        return $form;
    }

  /**
   * Helper: Build a step title.
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
   * Step 1: Enter your phone number.
   */
    protected function buildPhoneStep(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 1: Enter Your Phone Number');
        $form['phone'] = [
        '#type' => 'tel',
        '#title' => $this->t('Phone Number'),
        '#required' => true,
        '#description' => $this->t('Enter the phone number used in your appointment.'),
        ];
        $form['actions'] = $this->buildActions([
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitPhoneStep'],
        ],
        ]);
        return $form;
    }

  /**
   * Submit handler for Step 1.
   *
   * Save the phone number and go to Step 2.
   */
    public function submitPhoneStep(array &$form, FormStateInterface $form_state)
    {
        $phone = $form_state->getValue('phone');
        $form_state->set('phone', $phone);
      // Proceed to the next step to select an appointment.
        $form_state->set('step', 2);
        $form_state->setRebuild(true);
    }

  /**
   * Step 2: Choose an appointment from those found by phone.
   */
    protected function buildAppointmentSelection(array $form, FormStateInterface $form_state)
    {
        $phone = $form_state->get('phone');
      // Query for appointments matching the phone number.
        $ids = $this->entityTypeManager->getStorage('appointment')->getQuery()
        ->condition('customer_phone', $phone)
        ->accessCheck(false)
        ->execute();
        if (empty($ids)) {
            $this->messenger()->addWarning($this->t('No appointments found for phone: %phone', ['%phone' => $phone]));
          // Return to the phone step.
            $form_state->set('step', 1);
            $form_state->setRebuild(true);
            return $this->buildPhoneStep($form, $form_state);
        }

        $appointments = $this->entityTypeManager->getStorage('appointment')->loadMultiple($ids);
        $options = [];
        foreach ($appointments as $appointment) {
          // Build a label using (for example) title and date.
            $options[$appointment->id()] = $appointment->get('title')->value . ' (' . substr($appointment->get('appointment_date')->value, 0, 10) . ')';
        }

        $form['step_title'] = $this->buildStepTitle('Step 2: Select Appointment');
        $form['appointment'] = [
        '#type' => 'select',
        '#title' => $this->t('Your Appointments'),
        '#options' => $options,
        '#empty_option' => $this->t('- Select an appointment -'),
        '#required' => true,
        ];
        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPhoneStep'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitAppointmentSelection'],
        ],
        ]);
        return $form;
    }

  /**
   * Back handler for Step 2.
   */
    public function backToPhoneStep(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('step', 1);
        $form_state->setRebuild(true);
    }

  /**
   * Submit handler for Step 2.
   *
   * Save the appointment ID and load its data into form state.
   */
    public function submitAppointmentSelection(array &$form, FormStateInterface $form_state)
    {
        $appointment_id = $form_state->getValue('appointment');
        $form_state->set('appointment_id', $appointment_id);
      // Load the appointment so that we can pre-populate subsequent fields.
        $appointment = $this->entityTypeManager->getStorage('appointment')->load($appointment_id);
        if ($appointment) {
          // Save values to pre-populate later steps.
            $form_state->set('agency', $appointment->get('agency')->target_id);
          // For field_specializations, you may store the term ID if the appointment relates to it.
          // (Customize this as needed.)
            $form_state->set('field_specializations', $appointment->get('field_specializations')->target_id ?? null);
            $form_state->set('adviser', $appointment->get('adviser')->target_id);
            $form_state->set('date', substr($appointment->get('appointment_date')->value, 0, 10));
          // Extract time in "HH:MM" format.
            $date_obj = new \DateTime($appointment->get('appointment_date')->value);
            $form_state->set('time', $date_obj->format('H:i'));
            $form_state->set('name', $appointment->get('customer_name')->value);
            $form_state->set('email', $appointment->get('customer_email')->value);
            $form_state->set('notes', $appointment->get('notes')->value);
        }
      // Continue to Step 3.
        $form_state->set('step', 3);
        $form_state->setRebuild(true);
    }

  /**
   * Step 3: Modify Agency.
   *
   * This step is analogous to the original Step 1 in the booking form.
   */
    protected function buildStepOne(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 3: Modify Agency');
        $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
        $options = [];
        foreach ($agencies as $agency) {
            $options[$agency->id()] = $agency->label();
        }
        $default = $form_state->get('agency') ?: null;
        $form['agency'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Agency'),
        '#options' => $options,
        '#default_value' => $default,
        '#required' => true,
        '#empty_option' => $this->t('- Select an agency -'),
        ];
        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToAppointmentSelection'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Next'),
        '#submit' => ['::submitStepOne'],
        ],
        ]);
        return $form;
    }

  /**
   * Back handler for Step 3.
   */
    public function backToAppointmentSelection(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('step', 2);
        $form_state->setRebuild(true);
    }

  /**
   * Submit handler for Step 3.
   */
    public function submitStepOne(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('agency', $form_state->getValue('agency'));
        $form_state->set('step', 4);
        $form_state->setRebuild(true);
    }

  /**
   * Step 4: Modify field_Specializations.
   *
   * This step is analogous to the original Step 2 (select field_specializations).
   */
    protected function buildStepTwo(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 4: Modify field_Specializations');
      // Load taxonomy terms from the 'specializations' vocabulary.
        $terms = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'specializations']);
        $options = [];
        foreach ($terms as $term) {
            $options[$term->id()] = $term->label();
        }
        $default = $form_state->get('field_specializations') ?: null;
        $form['field_specializations'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Specialization'),
        '#options' => $options,
        '#default_value' => $default,
        '#required' => true,
        '#empty_option' => $this->t('- Select specialization -'),
        ];
        $form['actions'] = $this->buildActions([
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToPrevious'], // Goes back to Step 3.
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
   * Submit handler for Step 4.
   */
    public function submitStepTwo(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('field_specializations', $form_state->getValue('field_specializations'));
        $form_state->set('step', 5);
        $form_state->setRebuild(true);
    }

  /**
   * Step 5: Modify Adviser, Date and Time.
   *
   * This step is analogous to the original Step 3 for selecting an adviser
   * and setting a date/time.
   */
    protected function buildStepThree(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 5: Modify Adviser, Date and Time');

        $agency_id = $form_state->get('agency');
        $field_specializations_id = $form_state->get('field_specializations');

      // Load advisers matching agency and field_specializations.
        $query = $this->entityTypeManager->getStorage('user')->getQuery()
        ->condition('status', 1)
        ->condition('roles', 'adviser')
        ->condition('field_agency', $agency_id)
        ->condition('field_specializations', $field_specializations_id)
        ->accessCheck(true);
        $adviser_ids = $query->execute();
        $advisers = $this->entityTypeManager->getStorage('user')->loadMultiple($adviser_ids);
        $options = [];
        foreach ($advisers as $adviser) {
            $options[$adviser->id()] = $adviser->getDisplayName();
        }
        $default = $form_state->get('adviser') ?: null;
        $form['adviser'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Adviser'),
        '#options' => $options,
        '#default_value' => $default,
        '#required' => true,
        '#empty_option' => $this->t('- Select adviser -'),
        ];

      // For date and time, reuse the original fields. Prepopulate from stored values.
        $default_date = $form_state->get('date') ?: '';
        $default_time = $form_state->get('time') ?: '';

        $form['date'] = [
        '#type' => 'date',
        '#title' => $this->t('Appointment Date'),
        '#default_value' => $default_date,
        '#required' => true,
        ];
      // Note: Drupal core does not provide a '#type' => 'time' element.
      // Using a textfield with an HTML5 attribute "type" => "time".
        $form['time'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Appointment Time'),
        '#default_value' => $default_time,
        '#attributes' => ['type' => 'time'],
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
   * Submit handler for Step 5.
   */
    public function submitStepThree(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('adviser', $form_state->getValue('adviser'));
        $form_state->set('date', $form_state->getValue('date'));
        $form_state->set('time', $form_state->getValue('time'));
        $form_state->set('step', 6);
        $form_state->setRebuild(true);
    }

  /**
   * Step 6: Modify Personal Information.
   *
   * This step is analogous to the original Step 5.
   */
    protected function buildStepFour(array $form, FormStateInterface $form_state)
    {
        $form['step_title'] = $this->buildStepTitle('Step 6: Modify Personal Information');

        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Your Name'),
        '#default_value' => $form_state->get('name'),
        '#required' => true,
        ];
        $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#default_value' => $form_state->get('email'),
        '#required' => true,
        ];
        $form['phone'] = [
        '#type' => 'tel',
        '#title' => $this->t('Phone'),
        '#default_value' => $form_state->get('phone'),
        '#required' => true,
        ];
        $form['notes'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Notes'),
        '#default_value' => $form_state->get('notes'),
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
        '#value' => $this->t('Save Changes'),
        ],
        ]);
        return $form;
    }

  /**
   * Generic back handler that reduces step by one.
   */
    public function backToPrevious(array &$form, FormStateInterface $form_state)
    {
        $current_step = $form_state->get('step');
        $form_state->set('step', $current_step - 1);
        $form_state->setRebuild(true);
    }

  /**
   * Final submit handler.
   *
   * Update the appointment with new values.
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
            $appointment_id = $form_state->get('appointment_id');
            $appointment = $this->entityTypeManager->getStorage('appointment')->load($appointment_id);
            if (!$appointment) {
                throw new \Exception('Appointment not found.');
            }

          // (Optional) Verify that the phone number entered in Step 1 matches the appointment.
            $input_phone = $form_state->get('phone');
            if ($input_phone !== $appointment->get('customer_phone')->value) {
                throw new \Exception('The phone number does not match the appointment.');
            }

          // Normalize time.
            $time = (string) $form_state->get('time');
            if (strlen($time) == 4 && strpos($time, ':') === false) {
                $time = substr($time, 0, 2) . ':' . substr($time, 2, 2);
            }

          // Create a DateTime object from the new date and time.
            $date_time = \DateTime::createFromFormat('Y-m-d H:i', $form_state->get('date') . ' ' . $time);
            if (!$date_time) {
                throw new \Exception('Could not parse date/time combination.');
            }
            $formatted_datetime = $date_time->format('Y-m-d\TH:i:s');

          // Update the appointment with new values.
            $appointment->set('agency', $form_state->get('agency'));
            $appointment->set('field_specializations', $form_state->get('field_specializations'));
            $appointment->set('adviser', $form_state->get('adviser'));
            $appointment->set('appointment_date', $formatted_datetime);
          // Also update a time_slot field if needed.
            $appointment->set('time_slot', str_replace(':', '', $time));
            $appointment->set('customer_name', $form_state->getValue('name'));
            $appointment->set('customer_email', $form_state->getValue('email'));
            $appointment->set('customer_phone', $form_state->getValue('phone'));
            $appointment->set('notes', $form_state->getValue('notes'));
            $appointment->save();

            $this->messenger()->addMessage($this->t('Your appointment has been updated successfully.'));

          // (Optional) Send notification emails here.
            $form_state->setRedirectUrl(Url::fromUri('internal:/appointment/modification'));
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('There was a problem updating your appointment. Please try again.'));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
