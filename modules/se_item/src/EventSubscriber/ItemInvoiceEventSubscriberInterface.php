<?php

declare(strict_types=1);

namespace Drupal\se_item\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Item saving.
 *
 * @package Drupal\se_item\EventSubscriber
 */
interface ItemInvoiceEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * When an invoice is inserted, mark all items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoiceInsert(EntityInsertEvent $event);

  /**
   * When an invoice is inserted, mark all items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoiceUpdate(EntityUpdateEvent $event);

  /**
   * When an invoice is inserted, mark all items as back in stock.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemInvoicePresave(EntityPresaveEvent $event);

}
