<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_timekeeping\Entity\Timekeeping;
use Drupal\stratoserp\Constants;

/**
 * Class TimekeepingSaveEventSubscriber.
 *
 * Mark timekeeping entries status if they are included on an invoice.
 *
 * @package Drupal\se_timekeeping\EventSubscriber
 */
class TimekeepingInvoiceEventSubscriber implements TimekeepingInvoiceEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'timekeepingInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'timekeepingInvoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'timekeepingInvoicePresave',
      HookEventDispatcherInterface::ENTITY_DELETE => 'timekeepingInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity instanceof Invoice) {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity instanceof Invoice) {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity instanceof Invoice) {
      $this->timekeepingMarkItemsUnBilled($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity instanceof Invoice) {
      $this->timekeepingMarkItemsUnBilled($entity);
    }
  }

  /**
   * Loop through the invoice entries and mark the originals as required.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function timekeepingMarkItemsBilled(Invoice $invoice): void {
    $bundleFieldType = 'se_' . Constants::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $timekeeping->set('se_tk_billed', TRUE);
          $timekeeping->set('se_tk_invoice_ref', $invoice->id());
          $timekeeping->save();
        }
      }
    }
  }

  /**
   * Loop through the invoice entries and mark the originals as required.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The entity to update timekeeping items.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function timekeepingMarkItemsUnBilled(Invoice $invoice): void {
    $bundleFieldType = 'se_' . Constants::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'se_timekeeping') {
        /** @var \Drupal\se_timekeeping\Entity\Timekeeping $timekeeping */
        if ($timekeeping = Timekeeping::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $timekeeping->set('se_tk_billed', FALSE);
          $timekeeping->set('se_tk_invoice_ref', NULL);
          $timekeeping->save();
        }
      }
    }
  }

}
