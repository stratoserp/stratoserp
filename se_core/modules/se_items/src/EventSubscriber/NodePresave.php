<?php

namespace Drupal\se_items\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class NodePresave implements EventSubscriberInterface {

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
    if (($entity = $event->getEntity())
      && ($entity->getEntityTypeId() !== 'node'
        || $entity->bundle() !== 'se_invoice')) {
      return;
    }
    $total = 0;

    if (!array_key_exists($entity->bundle(), ErpCore::ITEMS_BUNDLE_MAP)) {
      return;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $items = $entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_items'}->referencedEntities();

    foreach ($items as $index => $ref_entity) {
      $total += $ref_entity->field_it_quantity->value * $ref_entity->field_it_price->value;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $entity->{'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()] . '_total'}->value = $total;
  }

}