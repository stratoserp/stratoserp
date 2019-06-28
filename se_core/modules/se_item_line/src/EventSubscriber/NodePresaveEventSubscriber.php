<?php

namespace Drupal\se_item_line\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NodePresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'nodePreSave',
    ];
  }

  /**
   * When one of the node types that has items in it is saved,
   * calculate the total of the items for saving.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function nodePreSave(EntityPresaveEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'node')) {
      return;
    }
    if (!in_array($entity->bundle(), ['se_invoice', 'se_quote', 'se_purchase_order', 'se_goods_receipt'])) {
      return;
    }
    $total = 0;

    if (!array_key_exists($entity->bundle(), ErpCore::ITEMS_BUNDLE_MAP)) {
      return;
    }

    foreach ($entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'} as $index => $item) {
      $total += $item->quantity * $item->price;
    }

    $entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_total'}->value =
      \Drupal::service('se_accounting.currency_format')->formatStorage((float)$total);
  }

}
