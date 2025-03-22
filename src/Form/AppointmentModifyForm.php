<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for modifying appointments via phone number.
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
   * Constructs a new AppointmentModifyForm object.
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
        return 'appointment_modify_form';
    }

  /**
   * Build the form.
   *
   * This form has two steps:
   *   1. Enter phone number and load matching appointments.
   *   2. Choose an appointment and modify its details.
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      // We can control the step using a form state value.
        $step = $form_state->get('modify_step') ?: 1;
        $form_state->set('modify_step', $step);

        switch ($step) {
            case 1:
                return $this->buildStepOne($form, $form_state);
            case 2:
                return $this->buildStepTwo($form, $form_state);
            case 3:
                return $this->buildStepThree($form, $form_state);
        }
    }

  /**
   * Step 1: Collect Phone Number.
   */
    protected function buildStepOne(array $form, FormStateInterface $form_state)
    {
        $form['description'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('Enter your phone number to view and modify your appointments.') . '</p>',
        ];
        $form['phone'] = [
        '#type' => 'tel',
        '#title' => $this->t('Phone Number'),
        '#required' => true,
        '#description' => $this->t('Please enter the phone number used when booking your appointment.'),
        ];
        $form['actions'] = [
        '#type' => 'actions',
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Load Appointments'),
        '#submit' => ['::submitStepOne'],
        ],
        ];
        return $form;
    }

  /**
   * Submission for Step 1.
   *
   * Loads appointments matching the phone number.
   */
    public function submitStepOne(array &$form, FormStateInterface $form_state)
    {
        $phone = $form_state->getValue('phone');
      // Save the phone number in form state for later use.
        $form_state->set('phone', $phone);

      // Query appointments using the phone number.
        $ids = $this->entityTypeManager->getStorage('appointment')->getQuery()
        ->condition('customer_phone', $phone)
        ->accessCheck(false)
        ->execute();
        $appointments = $this->entityTypeManager->getStorage('appointment')->loadMultiple($ids);

      // Save the loaded appointment IDs in the form state.
        $options = [];
        foreach ($appointments as $appointment) {
          // Use appointment ID as key and the title or date as label.
            $options[$appointment->id()] = $appointment->get('title')->value . ' (' . substr($appointment->get('appointment_date')->value, 0, 10) . ')';
        }

        if (empty($options)) {
            $this->messenger()->addWarning($this->t('No appointments found for the phone number %phone.', ['%phone' => $phone]));
          // Stay on step 1.
            $form_state->setRebuild(true);
            return;
        }
      // Store options in the form state.
        $form_state->set('appointment_options', $options);
      // Proceed to step 2.
        $form_state->set('modify_step', 2);
        $form_state->setRebuild(true);
    }

  /**
   * Step 2: Let the user select an appointment to modify.
   */
    protected function buildStepTwo(array $form, FormStateInterface $form_state)
    {
        $phone = $form_state->get('phone');
        $options = $form_state->get('appointment_options');

        $form['description'] = [
        '#type' => 'markup',
        '#markup' => '<p>' . $this->t('Select one of your appointments to modify.') . '</p>',
        ];
        $form['appointment'] = [
        '#type' => 'select',
        '#title' => $this->t('Your Appointments'),
        '#options' => $options,
        '#empty_option' => $this->t('- Select an appointment -'),
        '#required' => true,
        ];
        $form['actions'] = [
        '#type' => 'actions',
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Go Back'),
        '#submit' => ['::backToStepOne'],
        '#limit_validation_errors' => [],
        ],
        'next' => [
        '#type' => 'submit',
        '#value' => $this->t('Modify Appointment'),
        '#submit' => ['::submitStepTwo'],
        ],
        ];
        return $form;
    }

  /**
   * Submission for Step 2.
   */
    public function submitStepTwo(array &$form, FormStateInterface $form_state)
    {
      // Save the selected appointment ID.
        $appointment_id = $form_state->getValue('appointment');
        $form_state->set('appointment_id', $appointment_id);
      // Proceed to step 3: show the modify form.
        $form_state->set('modify_step', 3);
        $form_state->setRebuild(true);
    }

  /**
   * Step 3: Display the form to modify the appointment.
   */
    protected function buildStepThree(array $form, FormStateInterface $form_state)
    {
        $appointment_id = $form_state->get('appointment_id');
        $appointment = $this->entityTypeManager->getStorage('appointment')->load($appointment_id);

        if (!$appointment) {
            $this->messenger()->addError($this->t('Invalid appointment selected.'));
            $form_state->set('modify_step', 1);
            $form_state->setRebuild(true);
            return $this->buildStepOne($form, $form_state);
        }

      // Build fields to modify. For example, allow modification of notes or time.
        $form['notes'] = [
        '#type' => 'textarea',
        '#title' => $this->t('Notes'),
        '#default_value' => $appointment->get('notes')->value,
        ];

      // You could also allow the user to change the time, etc.
        $form['time'] = [
        '#type' => 'time',
        '#title' => $this->t('Appointment Time'),
        '#default_value' => $appointment->get('appointment_date')->value, // Ideally, extract time portion.
        '#required' => true,
        ];

        $form['actions'] = [
        '#type' => 'actions',
        'back' => [
        '#type' => 'submit',
        '#value' => $this->t('Back'),
        '#submit' => ['::backToStepTwo'],
        '#limit_validation_errors' => [],
        ],
        'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save Changes'),
        ],
        ];

        return $form;
    }

  /**
   * Final submit handler for modifying the appointment.
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      // This will be called when the user in step 3 submits the modified form.
        try {
            $appointment_id = $form_state->get('appointment_id');
            $appointment = $this->entityTypeManager->getStorage('appointment')->load($appointment_id);
            if (!$appointment) {
                throw new \Exception('Appointment not found.');
            }

          // Validate that the appointment belongs to the provided phone number.
            $input_phone = $form_state->get('phone');
            if ($input_phone !== $appointment->get('customer_phone')->value) {
                throw new \Exception('The phone number does not match this appointment.');
            }

          // Update appointment fields.
            $appointment->set('notes', $form_state->getValue('notes'));
          // For simplicity, we update the appointment_date's time portion.
          // (Here you might need additional logic to update the stored datetime.)
            $appointment->set('time_slot', str_replace(':', '', $form_state->getValue('time')));

            $appointment->save();
            $this->messenger()->addMessage($this->t('Your appointment has been updated successfully.'));
            $form_state->setRedirect('entity.appointment.canonical', ['appointment' => $appointment_id]);
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('There was a problem updating your appointment: @msg', ['@msg' => $e->getMessage()]));
        }
    }

  /**
   * Go back to step 1.
   */
    public function backToStepOne(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('modify_step', 1);
        $form_state->setRebuild(true);
    }

  /**
   * Go back to step 2.
   */
    public function backToStepTwo(array &$form, FormStateInterface $form_state)
    {
        $form_state->set('modify_step', 2);
        $form_state->setRebuild(true);
    }
}
