<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Appointment entity.
 */
class AppointmentAccessControlHandler extends EntityAccessControlHandler
{
  /**
   * {@inheritdoc}
   */
    protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account)
    {
        switch ($operation) {
            case 'view':
                return AccessResult::allowedIfHasPermission($account, 'view appointment');

            case 'update':
                return AccessResult::allowedIfHasPermission($account, 'edit appointment');

            case 'delete':
                return AccessResult::allowedIfHasPermission($account, 'delete appointment');
        }

        return AccessResult::neutral();
    }

  /**
   * {@inheritdoc}
   */
    protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = null)
    {
        return AccessResult::allowedIfHasPermission($account, 'create appointment');
    }
}
