<?php

namespace Drupal\appointment\Entity\Appointment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Interface for Appointment entities.
 */
interface AppointmentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface
{
  // Add custom method declarations if needed
}
