<?php

namespace Drupal\se_recurring\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceInsert implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'itemInsert',
    ];
  }

  /**
   * When an item is saved, create an associated stock item.
   *
   * @param EntityInsertEvent $event
   *
   */
  public function itemInsert(EntityInsertEvent $event) {
//    $node = $event->getNode();
//    if ($node->bundle() == 'se_invoice' && !$node->subscription_created) {
//      \Drupal::service('se_subscription.auto_creator')->create($node);
//      $event->setNode($node);
//    }
  }

}
