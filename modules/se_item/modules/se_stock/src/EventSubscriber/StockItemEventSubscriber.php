<?php

declare(strict_types=1);

namespace Drupal\se_stock\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_item\Entity\Item;

/**
 * Class StockItemPresaveEventSubscriber.
 *
 * Create 'parent' items for stock items if they don't already exist.
 *
 * @package Drupal\se_stock\EventSubscriber
 */
class StockItemEventSubscriber implements StockItemEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'stockItemPresave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function stockItemPresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'se_item') {
      return;
    }

    if ($entity->bundle() !== 'se_stock') {
      return;
    }

    if (!empty($entity->se_it_serial->value)) {
      $query = \Drupal::entityQuery('se_item')
        ->condition('type', 'se_stock')
        ->notExists('se_it_serial')
        ->condition('se_it_code', $entity->se_it_code->value);
      $items = $query->execute();

      /** @var \Drupal\se_item\Entity\Item $stockItem */
      if (empty($items)) {
        $stockItem = Item::create([
          'type' => 'se_stock',
          'user_id' => $entity->user_id->target_id,
          'name' => $entity->name->value,
          'se_it_code' => ['value' => $entity->se_it_code->value],
          'se_it_serial' => ['value' => ''],
          'se_it_sell_price' => ['value' => $entity->se_it_sell_price->value],
          'se_it_cost_price' => ['value' => $entity->se_it_cost_price->value],
        ]);
        if (isset($entity->se_it_product_type_ref)) {
          $stockItem->se_it_product_type_ref->target_id = $entity->se_it_product_type_ref->target_id;
        }
        if (isset($entity->se_it_manufacturer_ref)) {
          $stockItem->se_it_manufacturer_ref->target_id = $entity->se_it_manufacturer_ref->target_id;
        }
        if (isset($entity->se_it_sale_category_ref)) {
          $stockItem->se_it_sale_category_ref->target_id = $entity->se_it_sale_category_ref->target_id;
        }
        $stockItem->save();
      }
      else {
        $stockItem = Item::load(reset($items));
      }
    }

    if (isset($stockItem)) {
      $entity->se_it_item_ref->target_id = $stockItem->id();
    }

  }

}
