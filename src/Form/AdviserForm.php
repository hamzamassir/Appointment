<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Password\PasswordGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    public function __construct(
        EntityTypeManagerInterface $entity_type_manager,
        PasswordGeneratorInterface $password_generator
    ) {
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
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
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

      // Get all agencies
        $agencies = $this->entityTypeManager->getStorage('agency')
        ->loadMultiple();
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
        ];

      // Get all specializations
        $specializations = $this->entityTypeManager->getStorage('taxonomy_term')
        ->loadByProperties(['vid' => 'specializations']);
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

        $form['working_hours'] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Working Hours'),
        '#options' => [
        '09:00' => '09:00',
        '10:00' => '10:00',
        '11:00' => '11:00',
        '12:00' => '12:00',
        '13:00' => '13:00',
        '14:00' => '14:00',
        '15:00' => '15:00',
        '16:00' => '16:00',
        '17:00' => '17:00',
        ],
        '#required' => true,
        '#description' => $this->t('Select available working hours'),

        ];

        // Changed from password_confirm to regular password field
        $form['password'] = [
            '#type' => 'password',
            '#title' => $this->t('Password'),
            '#required' => true,
            '#attributes' => ['autocomplete' => 'new-password'],
        ];

        $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Create Adviser'),
        ];
        return $form;
    }

  /**
   * {@inheritdoc}
   */
    public function validateForm(array &$form, FormStateInterface $form_state)
    {
      // Check if email already exists
        $existing_user = $this->entityTypeManager->getStorage('user')
        ->loadByProperties(['mail' => $form_state->getValue('email')]);
        if (!empty($existing_user)) {
            $form_state->setErrorByName('email', $this->t('Email address already exists.'));
        }
    }

  /**
 * {@inheritdoc}
 */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        try {
        // Prepare the specializations data.
            $selected_specializations = array_filter($form_state->getValue('specializations'));
            $specializations = [];
            foreach ($selected_specializations as $id) {
                // Build the structure required for an entity reference field.
                $specializations[] = ['target_id' => $id];
            }

        // Prepare the working hours data.
            $selected_working_hours = array_filter($form_state->getValue('working_hours'));
            $working_hours = [];
            foreach ($selected_working_hours as $hour) {
                // Assuming your working hours field is configured as a list/text field.
                $working_hours[] = ['value' => $hour];
            }

        // Create new user with adviser role.
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

        // Redirect to user list.
            $form_state->setRedirect('entity.user.collection');
        } catch (\Exception $e) {
            $this->messenger()->addError($this->t('Error creating adviser: @error', [
            '@error' => $e->getMessage(),
            ]));
            \Drupal::logger('appointment')->error($e->getMessage());
        }
    }
}
