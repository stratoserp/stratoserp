<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\se_goods_receipt\Entity\GoodsReceipt;
use Drupal\se_goods_receipt\Service\GoodsReceiptServiceInterface;

/**
 * Class GoodsReceiptInsertEventSubscriber.
 *
 * Update the items with the goods receipt number after it has been saved.
 *
 * @package Drupal\se_goods_receipt\EventSubscriber
 */
class GoodsReceiptEventSubscriber implements GoodsReceiptEventSubscriberInterface {

  /** @var \Drupal\se_goods_receipt\Service\GoodsReceiptServiceInterface */
  protected GoodsReceiptServiceInterface $goodsReceiptService;

  public function __construct(GoodsReceiptServiceInterface $goodsReceiptService) {
    $this->goodsReceiptService = $goodsReceiptService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'itemLineEntityPresave',
      EntityHookEvents::ENTITY_INSERT => 'goodsReceiptItemsInsert',
      // HookEventDispatcherInterface::ENTITY_DELETE => '', // @todo delete.
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function itemLineEntityPresave(EntityPresaveEvent $event): void {
    $goodsReceipt = $event->getEntity();
    if (!$goodsReceipt instanceof GoodsReceipt) {
      return;
    }

    // Ensure that the items being received exist.
    $this->goodsReceiptService->createItems($goodsReceipt);
  }

  /**
   * {@inheritdoc}
   */
  public function goodsReceiptItemsInsert(EntityInsertEvent $event): void {
    $goodsReceipt = $event->getEntity();
    if (!$goodsReceipt instanceof GoodsReceipt) {
      return;
    }

    // Store the various references on the item.
    $this->goodsReceiptService->updateFields($goodsReceipt);
  }

}
