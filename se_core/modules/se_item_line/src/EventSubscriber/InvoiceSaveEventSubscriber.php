<?php

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * Update item status if they have been invoiced out.
 *
 * @package Drupal\se_item_line\EventSubscriber
 */
class InvoiceSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsertMarkSold',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdateMarkSold',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoiceMarkAvailable',
    ];
  }

  /**
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   */
  public function invoiceInsertMarkSold(EntityInsertEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity);
    }
  }

  /**
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   */
  public function invoiceUpdateMarkSold(EntityUpdateEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity);
    }
  }

  /**
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   */
  public function invoiceMarkAvailable(EntityPresaveEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->nodeMarkItemStatus($entity, FALSE);
    }
  }

  /**
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param \Drupal\node\Entity\Node $entity
   * @param bool $sold
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function nodeMarkItemStatus($entity, $sold = TRUE) {
    $bundle_field_type = 'field_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      /** @var \Drupal\se_item\Entity\Item $item */
      if (($item = Item::load($item_line->target_id)) && in_array($item->bundle(), ['se_stock', 'se_assembly'])) {
        // TODO - Make a service for this?
        if (!empty($item->field_it_item_ref->target_id)) {
          $date = new DrupalDateTime(NULL, drupal_get_user_timezone());
          $storage_date = \Drupal::service('date.formatter')->format($date->getTimestamp(), 'custom', 'Y-m-d', DateTimeItemInterface::STORAGE_TIMEZONE);
          $item->field_it_sale_date->value = $storage_date;
          $item->field_it_sale_price->value = $item_line->price;
          $item->field_it_invoice_ref->target_id = $entity->id();
          $item->save();
        }
      }
    }
  }

}
