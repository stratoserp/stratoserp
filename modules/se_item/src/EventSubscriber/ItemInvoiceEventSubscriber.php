<?php

declare(strict_types=1);

namespace Drupal\se_item\EventSubscriber;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * Update item status if they have been invoiced out.
 *
 * @package Drupal\se_item\EventSubscriber
 */
class ItemInvoiceEventSubscriber implements ItemInvoiceEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'itemInvoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemInvoicePresave',
      HookEventDispatcherInterface::ENTITY_DELETE => 'itemInvoiceDelete',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'se_invoice') {
      $this->markItemsSold($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'se_invoice') {
      $this->markItemsSold($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'se_invoice') {
      $this->markItemsAvailable($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function itemInvoiceDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'se_invoice') {
      $this->markItemsAvailable($entity);
    }
  }

  /**
   * Mark the items as sold/in stock as dictated by the parameter.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The node that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function markItemsSold(Invoice $invoice): void {
    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];
    $date = new DateTimePlus(NULL, date_default_timezone_get());

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      // Only operate on items.
      if ($itemLine->target_type === 'se_item') {
        // Only operate on stock items.
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($itemLine->target_id))
        && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
          // @todo Make a service for this?
          // Only operate on things that have a 'parent', not the parents
          // themselves, they are never sold.
          if (!empty($item->se_it_item_ref->target_id)) {
            $item
              ->set('se_it_sale_date', $date->format('Y-m-d'))
              ->set('se_it_sale_price', $itemLine->price)
              ->set('se_it_invoice_ref', $invoice->id());
            $item->save();
          }
        }
      }
    }
  }

  /**
   * Mark the items as sold/in stock as dictated by the parameter.
   *
   * @param \Drupal\se_invoice\Entity\Invoice $invoice
   *   The node that is being processed.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function markItemsAvailable(Invoice $invoice): void {
    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$invoice->bundle()];

    foreach ($invoice->{$bundleFieldType . '_lines'} as $itemLine) {
      // Only operate on items.
      if ($itemLine->target_type === 'se_item') {
        // Only operator on stock items.
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($itemLine->target_id))
        && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
          // @todo Make a service for this?
          // Only operate on things that have a 'parent', not the parents
          // themselves, they are never sold.
          if (!empty($item->se_it_item_ref->target_id)) {
            $item
              ->set('se_it_sale_date', NULL)
              ->set('se_it_sale_price', NULL)
              ->set('se_it_invoice_ref', NULL);
            $item->save();
          }
        }
      }
    }
  }

}
