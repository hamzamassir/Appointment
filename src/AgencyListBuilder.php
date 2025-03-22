<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides a list controller for the agency entity type.
 */
class AgencyListBuilder extends EntityListBuilder
{
  /**
   * {@inheritdoc}
   */
    public function buildHeader()
    {
        $header['name'] = $this->t('Name');
        $header['address'] = $this->t('Address');
        $header['phone'] = $this->t('Phone');
        $header['status'] = $this->t('Status');
        return $header + parent::buildHeader();
    }

  /**
   * {@inheritdoc}
   */
    public function buildRow(EntityInterface $entity)
    {
      /** @var \Drupal\appointment\Entity\Agency\Agency $entity */
        $row['name'] = $entity->toLink();
        $row['address'] = $entity->get('address')->value;
        $row['phone'] = $entity->get('phone')->value;
        $row['status'] = $entity->get('status')->value ? $this->t('Active') : $this->t('Inactive');
        return $row + parent::buildRow($entity);
    }
}
