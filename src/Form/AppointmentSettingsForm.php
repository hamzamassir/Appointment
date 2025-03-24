<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\appointment\Controller\AdviserController;
use Drupal\appointment\AgencyListBuilder;
use Drupal\appointment\AppointmentListBuilder;

/**
 * Provides a settings page for the Appointment module.
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

        $form['intro'] = [
        '#type' => 'markup',
        '#markup' => '<div class="appointment-settings-intro">
                       <h2>' . $this->t('Appointment Module Settings') . '</h2>
                       <p>' . $this->t('Use the tabs below to manage Advisers, Appointments, and Agencies. You can also export appointment data to CSV from here.') . '</p>
                    </div>',
        ];

      // Export CSV link.
        $form['export_csv'] = [
        '#type' => 'link',
        '#title' => $this->t('Export Data to CSV'),
        '#url' => Url::fromRoute('appointment.export_csv'),
        '#attributes' => [
        'class' => ['button', 'button--primary'],
        'style' => 'margin-bottom: 20px;',
        ],
        ];
      // Add Adviser.
        $form['links']['add_adviser'] = [
        '#type' => 'link',
        '#title' => $this->t('Add Adviser'),
        '#url' => Url::fromRoute('appointment.add_adviser'),
        '#attributes' => [
        'class' => ['button'],
        'style' => 'margin-bottom: 10px;',
        ],
        ];
          // Add Agency.
        $form['links']['add_agency'] = [
          '#type' => 'link',
          '#title' => $this->t('Add Agency'),
          '#url' => Url::fromRoute('entity.agency.add_form'),
          '#attributes' => [
            'class' => ['button'],
            'style' => 'margin-bottom: 10px;',
          ],
        ];

      // Create vertical tabs container.
        $form['management_tabs'] = [
        '#type' => 'vertical_tabs',
        '#default_tab' => 'edit-advisers',
        ];

      // ---------------------------------------------
      // Tab 1: Advisers List.
      // ---------------------------------------------
        $form['advisers'] = [
        '#type' => 'details',
        '#title' => $this->t('Advisers'),
        '#group' => 'management_tabs',
        '#open' => true,
        ];
      // Use the AdviserController to render the adviser list.
        $adviser_controller = AdviserController::create(\Drupal::getContainer());
        $form['advisers']['content'] = $adviser_controller->listAdvisers();

      // ---------------------------------------------
      // Tab 2: Appointments List.
      // ---------------------------------------------
        $form['appointments'] = [
        '#type' => 'details',
        '#title' => $this->t('Appointments'),
        '#group' => 'management_tabs',
        ];
        $appointment_list_builder = new AppointmentListBuilder(
            \Drupal::entityTypeManager()->getDefinition('appointment'),
            \Drupal::entityTypeManager()->getStorage('appointment'),
            \Drupal::service('date.formatter')
        );
        $form['appointments']['content'] = $appointment_list_builder->render();

      // ---------------------------------------------
      // Tab 3: Agencies List.
      // ---------------------------------------------
        $form['agencies'] = [
        '#type' => 'details',
        '#title' => $this->t('Agencies'),
        '#group' => 'management_tabs',
        ];
        $agency_list_builder = new AgencyListBuilder(
            \Drupal::entityTypeManager()->getDefinition('agency'),
            \Drupal::entityTypeManager()->getStorage('agency')
        );
        $form['agencies']['content'] = $agency_list_builder->render();

        return parent::buildForm($form, $form_state);
    }

  /**
   * {@inheritdoc}
   */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);
    }
}
