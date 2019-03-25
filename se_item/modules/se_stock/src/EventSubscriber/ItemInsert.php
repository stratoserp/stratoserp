<?php

namespace Drupal\se_stock\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemInsert implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemInsert',
    ];
  }

  /**
   * When a stock item is saved, if it has a serial number and no existing
   * stock item exists with no serial number, create one with no serial.
   *
   * @param EntityInsertEvent $event
   */
  public function itemInsert(EntityInsertEvent $event) {
    $entity = $event->getEntity();
    if (!empty($entity->field_it_serial->value) && $entity->bundle() === 'se_stock') {
      $query = \Drupal::entityQuery('se_item')
        ->condition('type', 'se_stock')
        ->notExists('field_it_serial')
        ->condition('field_it_code', $entity->field_it_code->value);
      $items = $query->execute();

      if (empty($items)) {
        $stock_item = Item::create([
          'type' => 'se_stock',
          'user_id' => $entity->user_id->target_id,
          'name' => $entity->name->value,
          'field_it_code' => ['value' => $entity->field_it_code->value],
          'field_it_serial' => ['value' => ''],
          'field_it_sell_price' => ['value' => $entity->field_it_sell_price->value],
          'field_it_cost_price' => ['value' => $entity->field_it_cost_price->value]
        ]);
        if (isset($entoty->field_it_product_type_ref)) {
          $stock_item->field_it_product_type_ref->target_id = $entity->field_it_product_type_ref->target_id;
        }
        if (isset($entity->field_it_manufacturer_ref)) {
          $stock_item->field_it_manufacturer_ref->target_id = $entity->field_it_manufacturer_ref->target_id;
        }
        if (isset($entity->field_it_category_ref)) {
          $stock_item->field_it_category_ref->target_id = $entity->field_it_category_ref->target_id;
        }
        $stock_item->save();
      }
    }
  }

}
