<?php

namespace Drupal\appointment;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a list controller for the appointment entity type.
 */
class AppointmentListBuilder extends EntityListBuilder
{
  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
    protected $dateFormatter;

  /**
   * Constructs a new AppointmentListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
    public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter)
    {
        parent::__construct($entity_type, $storage);
        $this->dateFormatter = $date_formatter;
    }

  /**
   * {@inheritdoc}
   */
    public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type)
    {
        return new static(
            $entity_type,
            $container->get('entity_type.manager')->getStorage($entity_type->id()),
            $container->get('date.formatter')
        );
    }

  /**
   * {@inheritdoc}
   */
    public function buildHeader()
    {
        $header['id'] = $this->t('ID');
        $header['title'] = $this->t('Title');
        $header['customer_name'] = $this->t('Customer');
        $header['appointment_date'] = $this->t('Date');
        $header['status'] = $this->t('Status');
        return $header + parent::buildHeader();
    }

  /**
   * {@inheritdoc}
   */
    public function buildRow(EntityInterface $entity)
    {
      /** @var \Drupal\appointment\Entity\Appointment\Appointment $entity */
        $row['id'] = $entity->id();
        $row['title'] = $entity->toLink();
        $row['customer_name'] = $entity->get('customer_name')->value;

      // Properly format the datetime field
        $date = $entity->get('appointment_date')->value;
        if ($date) {
            $datetime = new \DateTime($date);
            $timestamp = $datetime->getTimestamp();
            $row['appointment_date'] = $this->dateFormatter->format($timestamp, 'custom', 'Y-m-d H:i');
        } else {
            $row['appointment_date'] = 'N/A';
        }

        $row['status'] = $entity->get('status')->value;
        return $row + parent::buildRow($entity);
    }
}
