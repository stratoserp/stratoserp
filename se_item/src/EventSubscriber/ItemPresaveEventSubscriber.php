<?php

namespace Drupal\se_item\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ItemPresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'itemPresave',
    ];
  }

  /**
   * @param EntityPresaveEvent $event
   */
  public function itemPresave(EntityPresaveEvent $event) {
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'se_item')) {
      return;
    }

    /** @var \Drupal\se_item\Entity\Item $entity value */
    switch ($entity->bundle()) {
      case 'se_service':
        $entity->field_it_cost_price->value =
          \Drupal::service('se_accounting.currency_format')->formatStorage($entity->field_it_cost_price->value ?? 0);
        $entity->field_it_sell_price->value =
          \Drupal::service('se_accounting.currency_format')->formatStorage($entity->field_it_sell_price->value ?? 0);
        break;
      case 'se_stock':
        $entity->field_it_cost_price->value =
          \Drupal::service('se_accounting.currency_format')->formatStorage($entity->field_it_cost_price->value ?? 0);
        $entity->field_it_sell_price->value =
          \Drupal::service('se_accounting.currency_format')->formatStorage($entity->field_it_sell_price->value ?? 0);
        $entity->field_it_sale_price->value =
          \Drupal::service('se_accounting.currency_format')->formatStorage($entity->field_it_sale_price->value ?? 0);
        break;

    }

  }
}