<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityCreateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PurchaseOrderInsertSubscriber.
 *
 * On inserting a purchase order, do things.
 *
 * @package Drupal\se_purchase_order\EventSubscriber
 */
class PurchaseOrderInsertSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_CREATE => 'purchaseOrderInsert',
    ];
  }

  /**
   * When a purchase order is saved, process its event.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityCreateEvent $event
   *   The event we are working with.
   */
  public function purchaseOrderInsert(EntityCreateEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    // TODO: Stuff here?
  }

}
