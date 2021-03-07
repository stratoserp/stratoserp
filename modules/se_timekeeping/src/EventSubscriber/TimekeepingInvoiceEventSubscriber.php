<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\comment\Entity\Comment;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\stratoserp\ErpCore;

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
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($invoice);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($invoice);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() === 'se_invoice') {
      $this->timekeepingMarkItemsUnBilled($invoice);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function timekeepingInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    $invoice = $event->getEntity();

    if ($invoice->getEntityTypeId() === 'se_invoice') {
      $this->timekeepingMarkItemsUnBilled($invoice);
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
    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'comment') {
        /** @var \Drupal\comment\Entity\Comment $comment */
        if ($comment = Comment::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $comment->set('se_tk_billed', TRUE);
          $comment->set('se_tk_invoice_ref', $invoice->id());
          $comment->save();
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
    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'comment') {
        /** @var \Drupal\comment\Entity\Comment $comment */
        if ($comment = Comment::load($itemLine->target_id)) {
          // @todo Make a service for this?
          $comment->set('se_tk_billed', FALSE);
          $comment->set('se_tk_invoice_ref', NULL);
          $comment->save();
        }
      }
    }
  }

}
