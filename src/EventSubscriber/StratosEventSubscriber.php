<?php

declare(strict_types=1);

namespace Drupal\stratoserp\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPredeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\se_invoice\Entity\Invoice;
use Drupal\se_payment\Entity\Payment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class StratosEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_DELETE => ['commonItemDelete', 95],
      EntityHookEvents::ENTITY_PRE_SAVE => ['commonItemPresave', 95]
    ];
  }

  /**
   * Some functionality of StratosERP requires the previous version.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPredeleteEvent $event
   *   The event that was triggered.
   *
   * @return void
   */
  public function commonItemDelete(EntityPredeleteEvent $event): void {
    $entity = $event->getEntity();
    $this->commonEvent($entity);
  }

  /**
   * Some functionality of StratosERP requires the previous version.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event that was triggered.
   *
   * @return void
   */
  public function commonItemPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();
    $this->commonEvent($entity);
  }

  /**
   * Perform the actual legwork.
   */
  private function commonEvent($entity) {
    if ($entity instanceof Invoice) {
      $entity->storeOldInvoice();
      return;
    }

    if ($entity instanceof Payment) {
      $entity->storeOldPayment();
      return;
    }

  }

}
