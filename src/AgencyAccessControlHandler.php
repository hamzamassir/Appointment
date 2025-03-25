<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Appointment entity.
 */
class AgencyAccessControlHandler extends EntityAccessControlHandler
{
  /**
   * {@inheritdoc}
   */
    protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account)
    {
        switch ($operation) {
            case 'view':
                return AccessResult::allowedIfHasPermission($account, 'view agency');

            case 'update':
                return AccessResult::allowedIfHasPermission($account, 'edit agency');

            case 'delete':
                return AccessResult::allowedIfHasPermission($account, 'delete agency');
        }

        return AccessResult::neutral();
    }

  /**
   * {@inheritdoc}
   */
    protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = null)
    {
        return AccessResult::allowedIfHasPermission($account, 'create agency');
    }
}
