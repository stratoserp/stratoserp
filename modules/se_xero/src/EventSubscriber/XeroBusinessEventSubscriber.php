<?php

declare(strict_types=1);

namespace Drupal\se_xero\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_business\Entity\Business;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class BusinessInsertEventSubscriber.
 *
 * When a business is added or updated, sync through to xero.
 *
 * @package Drupal\se_xero\EventSubscriber
 */
class XeroBusinessEventSubscriber implements EventSubscriberInterface {

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
    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $event->getEntity();
    if (!$business instanceof Business) {
      return;
    }

    if ($business->getSkipBusinessXeroEvents($business)) {
      return;
    }

    \Drupal::service('se_xero.contact_service')->sync($business);
  }

  /**
   * When a business is updated, update in Xero.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event to work with.
   */
  public function xeroBusinessUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_business\Entity\Business $business */
    $business = $event->getEntity();
    if (!$business instanceof Business) {
      return;
    }

    if ($business->getSkipBusinessXeroEvents($business)) {
      return;
    }

    \Drupal::service('se_xero.contact_service')->sync($business);
  }

}
