<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a settings form for the Appointment module.
 */
class AppointmentSettingsForm extends ConfigFormBase
{
  /**
   * {@inheritdoc}
   */
    public function getFormId()
    {
        return 'appointment_settings_form';
    }

  /**
   * {@inheritdoc}
   */
    protected function getEditableConfigNames()
    {
        return ['appointment.settings'];
    }

  /**
   * Build the module settings form.
   */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $form['export_heading'] = [
        '#type' => 'markup',
        '#markup' => '<h3>' . $this->t('Export Data') . '</h3>',
        ];

      // Create a link (styled as a button) pointing to the export route.
        $form['export_csv'] = [
        '#type' => 'link',
        '#title' => $this->t('Export Agencies, Advisors, and Appointments to CSV'),
        '#url' => Url::fromRoute('appointment.export_csv'),
        '#attributes' => ['class' => ['button']],
        ];

        return parent::buildForm($form, $form_state);
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
      // Save any settings if necessary.
        parent::submitForm($form, $form_state);
    }
}
