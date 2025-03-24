<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user\Entity\User;

/**
 * Provides a form for creating advisers.
 */
class AdviserForm extends FormBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * The password generator.
   *
   * @var \Drupal\Core\Password\PasswordGeneratorInterface
   */
    protected $passwordGenerator;

  /**
   * Constructs a new AdviserForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Password\PasswordGeneratorInterface $password_generator
   *   The password generator.
   */
    public function __construct(EntityTypeManagerInterface $entity_type_manager, PasswordGeneratorInterface $password_generator)
    {
        $this->entityTypeManager = $entity_type_manager;
        $this->passwordGenerator = $password_generator;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager'),
            $container->get('password_generator')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'appointment_adviser_form';
    }

  /**
   * Build the adviser creation form.
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
      // Adviser basic details.
        $form['name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#required' => true,
        ];

        $form['email'] = [
        '#type' => 'email',
        '#title' => $this->t('Email'),
        '#required' => true,
        ];

      // Agency select.
        $agencies = $this->entityTypeManager->getStorage('agency')->loadMultiple();
        $agency_options = [];
        foreach ($agencies as $agency) {
            $agency_options[$agency->id()] = $agency->label();
        }
        $form['agency'] = [
        '#type' => 'select',
        '#title' => $this->t('Agency'),
        '#options' => $agency_options,
        '#required' => true,
        '#empty_option' => $this->t('- Select an agency -'),
        '#ajax' => [
        'callback' => '::updateWorkingHours',
        'wrapper' => 'working-hours-wrapper',
        'event' => 'change',
        ],
        ];

      // Specializations checkboxes.
        $specializations = $this->entityTypeManager->getStorage('taxonomy_term')->loadByProperties(['vid' => 'specializations']);
        $specialization_options = [];
        foreach ($specializations as $term) {
            $specialization_options[$term->id()] = $term->label();
        }
        $form['specializations'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Specializations'),
        '#options' => $specialization_options,
        '#required' => true,
        ];

      // Working hours: dynamically generated based on the selected agency.
        $agency_id = $form_state->getValue('agency');
        $working_hours_options = [];
        if (!empty($agency_id)) {
            $agency = $this->entityTypeManager->getStorage('agency')->load($agency_id);
            if ($agency) {
                $start_time = $agency->get('operating_hours_start')->value;
                $end_time = $agency->get('operating_hours_end')->value;
                if (!empty($start_time) && !empty($end_time)) {
                    $start = strtotime($start_time);
                    $end = strtotime($end_time);
                    // Generate options in one-hour increments.
                    for ($time = $start; $time < $end; $time += 3600) {
                        $formatted = date('H:i', $time);
                        $working_hours_options[$formatted] = $formatted;
                    }
                }
            }
        } else {
          // No agency selected yet.
            $working_hours_options = [];
        }
        $form['working_hours'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Working Hours'),
        '#options' => $working_hours_options,
        '#required' => true,
        '#description' => $this->t('Select the available working hours for the adviser based on the agency operating hours.'),
        '#prefix' => '<div id="working-hours-wrapper">',
        '#suffix' => '</div>',
        ];

      // Password field.
        $form['password'] = [
        '#type' => 'password',
        '#title' => $this->t('Password'),
        '#required' => true,
        '#attributes' => ['autocomplete' => 'new-password'],
        ];

      // Submit button.
        $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Create Adviser'),
        ];

        return $form;
    }

  /**
   * AJAX callback to update the working hours based on the selected agency.
   */
    public function updateWorkingHours(array &$form, FormStateInterface $form_state)
    {
        return $form['working_hours'];
    }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      // Check if email already exists.
        $existing_users = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(['mail' => $form_state->getValue('email')]);
        if (!empty($existing_users)) {
            $form_state->setErrorByName('email', $this->t('Email address already exists.'));
        }
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
          // Prepare specializations data.
            $selected_specializations = array_filter($form_state->getValue('specializations'));
            $specializations = [];
            foreach ($selected_specializations as $id) {
                $specializations[] = ['target_id' => $id];
            }

          // Prepare working hours data.
            $selected_working_hours = array_filter($form_state->getValue('working_hours'));
            $working_hours = [];
            foreach ($selected_working_hours as $hour) {
                $working_hours[] = ['value' => $hour];
            }

          // Create new adviser user.
            $user = $this->entityTypeManager->getStorage('user')->create([
            'name' => $form_state->getValue('name'),
            'mail' => $form_state->getValue('email'),
            'pass' => $form_state->getValue('password'),
            'status' => 1,
            'roles' => ['adviser'],
            'field_agency' => $form_state->getValue('agency'),
            'field_specializations' => $specializations,
            'field_working_hours' => $working_hours,
            ]);
            $user->save();

            $this->messenger()->addMessage($this->t('Adviser @name created successfully.', [
            '@name' => $form_state->getValue('name'),
            ]));
            $form_state->setRedirect('entity.user.collection');
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Error creating adviser: @error', [
            '@error' => $e->getMessage(),
            ]));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
