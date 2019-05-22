<?php

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\se_core\ErpCore;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

class ItemsPostSave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemsPostSave',
    ];
  }

  /**
   * For goods receipts, we can update the items with the goods receipt number
   * After the goods receipt has been saved.
   *
   * @param EntityInsertEvent $event
   *
   */
  public function itemsPostSave(EntityInsertEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_goods_receipt') {
      foreach ($entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'} as $index => $value) {
        $new_value = $value->getValue();
        /** @var Paragraph $paragraph */
        $paragraph = Paragraph::load($new_value['target_id']);
        /** @var Item $item */
        if ($item = Item::load($paragraph->field_it_line_item->target_id)) {
          if ($item->bundle() !== 'se_stock') {
            continue;
          }
          $item->set('field_it_goods_receipt_ref', $entity->id());
          $item->set('field_it_purchase_order_ref', $entity->field_gr_purchase_order_ref->target_id);
          $item->save();
        }
      }
    }
  }
}
