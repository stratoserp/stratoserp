<?php

declare(strict_types=1);

namespace Drupal\se_purchase_order\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityCreateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
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
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityCreateEvent $event
   */
  public function purchaseOrderInsert(EntityCreateEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    // TODO
  }

}
