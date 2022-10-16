<?php

declare(strict_types=1);

namespace Drupal\se_stock\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Stock Item saving.
 *
 * @package Drupal\se_stock\EventSubscriber
 */
interface StockItemEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Update stock item.
   *
   * When a stock item is saved, if it has a serial number and no existing
   * stock item exists with no serial number, create one with no serial as
   * a 'parent' item to be used in quotes etc.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function stockItemPresave(EntityPresaveEvent $event);

  /**
   * When an invoice is inserted, mark all stock items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function stockItemInvoiceInsert(EntityInsertEvent $event);

  /**
   * When an invoice is inserted, mark all stock items as sold.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function stockItemInvoiceUpdate(EntityUpdateEvent $event);

  /**
   * When an invoice is inserted, mark all stock items as back in stock.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function stockItemInvoiceDelete(EntityDeleteEvent $event);

}
