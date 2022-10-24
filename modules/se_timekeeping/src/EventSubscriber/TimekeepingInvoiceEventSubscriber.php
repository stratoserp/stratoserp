<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_timekeeping\Service\TimeKeepingServiceInterface;

/**
 * Class TimekeepingSaveEventSubscriber.
 *
 * Mark timekeeping entries status if they are included on an invoice.
 *
 * @package Drupal\se_timekeeping\EventSubscriber
 */
class TimekeepingInvoiceEventSubscriber implements TimekeepingInvoiceEventSubscriberInterface {

  /** @var \Drupal\se_timekeeping\Service\TimeKeepingServiceInterface */
  protected TimeKeepingServiceInterface $timeKeepingService;

  public function __construct(TimeKeepingServiceInterface $timeKeepingService) {
    $this->timeKeepingService = $timeKeepingService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => 'timekeepingInvoiceInsert',
      EntityHookEvents::ENTITY_UPDATE => 'timekeepingInvoiceUpdate',
      EntityHookEvents::ENTITY_DELETE => 'timekeepingInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();
    if (!$invoice instanceof Invoice) {
      return;
    }

    $this->timeKeepingService->reconcileTimekeeping($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if (!$invoice instanceof Invoice) {
      return;
    }

    $this->timeKeepingService->reconcileTimekeeping($invoice);
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if (!$invoice instanceof Invoice) {
      return;
    }

    $this->timeKeepingService->markTimekeepingAvailableSave($invoice);
  }

}
