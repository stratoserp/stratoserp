<?php

namespace Drupal\se_subscription\EventSubscriber;

use Drupal\se_core\Event\SeCoreEvent;
use Drupal\se_core\Event\SeCoreEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class InvoiceInsert implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SeCoreEvents::NODE_CREATED => 'onInsert',
      SeCoreEvents::NODE_UPDATED => 'onInsert',
    ];
  }

  /**
   * When an item is saved, create an associated stock item.
   *
   * @param SeCoreEvent $event
   *
   */
  public function onInsert(SeCoreEvent $event) {
    $node = $event->getNode();
    if ($node->bundle() == 'se_invoice' && !$node->subscription_created) {
      \Drupal::service('se_subscription.auto_creator')->create($node);
      $event->setNode($node);
    }
  }

}
