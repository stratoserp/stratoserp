<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;

/**
 * Our extensions to the event subscriber for Goods receipt saving.
 *
 * @package Drupal\se_goods_receipt\EventSubscriber
 */
interface GoodsReceiptEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * Process items on goods receipt saving.
   *
   * For goods receipts, We need to make new stock items for everything that
   * has a serial number, and then update the list of items with the new
   * stock item ids.
   *
   * @todo Config option to generate serial numbers if blank?
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function itemLineEntityPresave(EntityPresaveEvent $event);

  /**
   * Update item details.
   *
   * For goods receipts, we can update the items with the goods receipt number
   * After the goods receipt has been saved.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function goodsReceiptItemsInsert(EntityInsertEvent $event);

}
