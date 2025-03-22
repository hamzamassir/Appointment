<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for selecting an agency.
 */
class AgencySelectionForm extends FormBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new AgencySelectionForm.
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
        return 'agency_selection_form';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $agencies = $this->entityTypeManager->getStorage('agency')
        ->loadByProperties(['status' => 1]);

        $options = [];
        foreach ($agencies as $agency) {
            $options[$agency->id()] = $agency->label();
        }

        $form['agency'] = [
        '#type' => 'select',
        '#title' => $this->t('Select Agency'),
        '#options' => $options,
        '#required' => true,
        ];

        $form['actions'] = [
        '#type' => 'actions',
        ];

        $form['actions']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Continue to Appointment'),
        ];

        return $form;
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        $agency_id = $form_state->getValue('agency');
        $form_state->setRedirect('entity.appointment.add_form', ['agency' => $agency_id]);
    }
}
