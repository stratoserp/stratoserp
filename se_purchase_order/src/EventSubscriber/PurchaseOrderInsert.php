<?php

namespace Drupal\se_purchase_order\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityCreateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PurchaseOrderInsert implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_CREATE => 'purchaseOrderInsert',
    ];
  }

  /**
   * @param EntityCreateEvent $event
   *
   */
  public function purchaseOrderInsert(EntityCreateEvent $event) {
    // TODO
  }

}