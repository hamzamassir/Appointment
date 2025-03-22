<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for an appointment entity type.
 */
class AppointmentSettingsForm extends FormBase
{
  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'appointment_settings';
    }

  /**
   * {@inheritdoc}
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['appointment_settings'] = [
        '#markup' => $this->t('Settings form for appointment entities. Add your custom settings here.'),
        ];

        return $form;
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      // Empty implementation of the abstract submit class.
    }
}
