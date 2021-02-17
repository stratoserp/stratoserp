<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\Traits\ErpEventTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BusinessInsertEventSubscriber.
 *
 * When a business is added or updated, sync through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class XeroBusinessEventSubscriber implements EventSubscriberInterface {

  use ErpEventTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'xeroBusinessInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'xeroBusinessUpdate',
    ];
  }

  /**
   * When a business is inserted, create the same business in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event to work with.
   */
  public function xeroBusinessInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($this->isSkipBusinessXeroEvents($entity)) {
      return;
    }

    if ($entity->bundle() === 'se_business' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($entity);
    }
  }

  /**
   * When a business is updated, update in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event to work with.
   */
  public function xeroBusinessUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    if ($this->isSkipBusinessXeroEvents($entity)) {
      return;
    }

    if ($entity->bundle() === 'se_business' && !$entity->get('se_xero_uuid')) {
      \Drupal::service('se_xero.contact_service')->sync($entity);
    }
  }

}
