<?php

namespace Drupal\appointment\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\webform\WebformInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Controller for appointment booking.
 */
class AppointmentController extends ControllerBase
{
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
    protected $entityTypeManager;

  /**
   * Constructs a new AppointmentController.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
    public function __construct(EntityTypeManagerInterface $entity_type_manager)
    {
        $this->entityTypeManager = $entity_type_manager;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container)
    {
        return new static(
            $container->get('entity_type.manager')
        );
    }

  /**
   * Displays the appointment booking form.
   *
   * @return array
   *   A render array representing the booking form.
   */
    public function bookingForm()
    {
        $webform = $this->entityTypeManager
        ->getStorage('webform')
        ->load('appointment_booking');

        if (!$webform instanceof WebformInterface) {
            return [
            '#markup' => $this->t('Appointment booking form is not available.'),
            ];
        }

        return $webform->getSubmissionForm();
    }
}
