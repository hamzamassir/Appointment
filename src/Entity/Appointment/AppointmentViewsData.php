<?php

namespace Drupal\appointment\Entity\Appointment;

use Drupal\views\EntityViewsData;

/**
 * Provides Views data for Appointment entities.
 */
class AppointmentViewsData extends EntityViewsData
{
    /**
     * {@inheritdoc}
     */
    public function getViewsData()
    {
        $data = parent::getViewsData();

        // Additional field for the appointment table.
        $data['appointment']['table']['base'] = [
        'field' => 'id',
        'title' => $this->t('Appointment'),
        'help' => $this->t('The Appointment entity ID.'),
        ];

        return $data;
    }
}
