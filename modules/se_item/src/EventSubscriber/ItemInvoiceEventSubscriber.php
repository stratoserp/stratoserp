<?php

declare(strict_types=1);

namespace Drupal\se_item\EventSubscriber;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\Entity\Node;
use Drupal\stratoserp\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * Update item status if they have been invoiced out.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class ItemInvoiceEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'itemInvoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemInvoicePresave',
    ];
  }

  /**
   * When an invoice is inserted, mark all items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity);
    }
  }

  /**
   * When an invoice is inserted, mark all items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity);
    }
  }

  /**
   * When an invoice is inserted, mark all items as back in stock.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity, FALSE);
    }
  }

  /**
   * Mark the items as sold/in stock as dictated by the parameter.
   *
   * @param \Drupal\node\Entity\Node $entity
   *   The node that is being processed.
   * @param bool $sold
   *   Whether to update as sold or returned.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function nodeMarkItemStatus(Node $entity, $sold = TRUE): void {
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
    $date = new DateTimePlus(NULL, date_default_timezone_get());

    foreach ($entity->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'se_item') {
        /** @var \Drupal\se_item\Entity\Item $item */
        if (($item = Item::load($itemLine->target_id))
        && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
          // @todo Make a service for this?
          // Only operate on things that have a 'parent', not the parents
          // themselves, they are never sold.
          if (!empty($item->se_it_item_ref->target_id)) {
            if (isset($item->se_it_invoice_ref->value) !== $sold) {
              if ($sold) {
                $item
                  ->set('se_it_sale_date', $date->format('Y-m-d'))
                  ->set('se_it_sale_price', $itemLine->price)
                  ->set('se_it_invoice_ref', $entity->id());
              }
              else {
                $item
                  ->set('se_it_sale_date', NULL)
                  ->set('se_it_sale_price', NULL)
                  ->set('se_it_invoice_ref', NULL);
              }
              $item->save();
            }
          }
        }
      }
    }
  }

}
