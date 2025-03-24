<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Drupal\Core\Link;
use Drupal\user\Entity\User;

/**
 * Controller for displaying the Adviser list.
 */
class AdviserController extends ControllerBase
{
  /**
   * Returns a render array containing a table of advisers.
   *
   * Only users with the 'adviser' role will be listed.
   *
   * @return array
   *   A render array.
   */
    public function listAdvisers()
    {
      // Build an entity query to load user entities.
        $query = \Drupal::entityQuery('user')
        ->accessCheck(true)
        ->condition('status', 1)
        ->condition('roles', 'adviser'); // Filter by the adviser role.

        $uids = $query->execute();

      // Load user entities for the given IDs.
        $users = User::loadMultiple($uids);

      // Prepare table header.
        $header = [
        $this->t('ID'),
        $this->t('Name'),
        $this->t('Email'),
        $this->t('Agency'),
        ];

      // Prepare table rows.
        $rows = [];
        foreach ($users as $user) {
          // Load associated agency label if available.
            $agency = $this->t('N/A');
            if ($user->hasField('field_agency') && !$user->get('field_agency')->isEmpty()) {
                $agency_entity = \Drupal::entityTypeManager()
                ->getStorage('agency')
                ->load($user->get('field_agency')->target_id);
                $agency = $agency_entity ? $agency_entity->label() : $agency;
            }

          // Build a row.
            $rows[] = [
            $user->id(),
            $user->toLink()->toString(),
            $user->getEmail(),
            $agency,
            ];
        }

      // Create the render array for the table.
        $build = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No advisers found.'),
        ];
        return $build;
    }
}
