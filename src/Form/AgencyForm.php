<?php

namespace Drupal\appointment\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the agency entity edit forms.
 */
class AgencyForm extends ContentEntityForm
{
    /**
     * {@inheritdoc}
     */
    public function save(array $form, FormStateInterface $form_state)
    {
        $result = parent::save($form, $form_state);
        $entity = $this->getEntity();

        $message_arguments = ['%label' => $entity->label()];
        $message = $result == SAVED_NEW
        ? $this->t('Created new agency %label.', $message_arguments)
        : $this->t('Updated agency %label.', $message_arguments);
        $this->messenger()->addStatus($message);

        $form_state->setRedirect('entity.agency.collection');
        return $result;
    }
}
