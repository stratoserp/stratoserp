<?php

declare(strict_types=1);

namespace Drupal\se_accounting\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Interface for the tax event subscriber.
 */
interface TaxEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Add/Update the tax amount before the entity is saved.
   */
  public function taxPreAction(EntityPresaveEvent $event): void;

}
