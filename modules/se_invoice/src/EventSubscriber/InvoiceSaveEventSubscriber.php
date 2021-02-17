<?php

declare(strict_types=1);

namespace Drupal\se_invoice\EventSubscriber;

use Drupal\Core\Entity\EntityInterface;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\ErpCore;
use Drupal\stratoserp\Traits\ErpEventTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class InvoiceSaveEventSubscriber.
 *
 * When an invoice is saved, adjust the business
 * balance by the amount of the invoice.
 *
 * @see \Drupal\se_payment\EventSubscriber\PaymentEventSubscriber
 *
 * @package Drupal\se_invoice\EventSubscriber
 */
class InvoiceSaveEventSubscriber implements EventSubscriberInterface {

  use ErpEventTrait;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'invoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'invoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'invoiceAdjust',
    ];
  }

  /**
   * Add the total of this invoice to the amount the business owes.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The event we are working with.
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_invoice') {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    $this->updateBusinessBalance($entity);
  }

  /**
   * Add the total of this invoice to the amount the business owes.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The event we are working with.
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node'
      || $entity->bundle() !== 'se_invoice') {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    $this->updateBusinessBalance($entity);
  }

  /**
   * Reduce the business balance by the amount of the old invoice.
   *
   * This need to be done in case the amount changes on the saving
   * of this invoice.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function invoiceAdjust(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'node'
      || $entity->isNew()
      || $entity->bundle() !== 'se_invoice') {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    // Is this the right way?
    $this->updateBusinessBalance($entity, TRUE);
  }

  /**
   * On invoicing, update the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The event we are working with.
   * @param bool $reduce_balance
   *   Whether we are increasing or reducing the balance.
   *
   * @return void|int
   *   The new balance is returned.
   */
  private function updateBusinessBalance(EntityInterface $entity, $reduce_balance = FALSE): int {
    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($entity)) {
      \Drupal::logger('se_business_invoice_save')->error('No business set for %node', ['%node' => $entity->id()]);
      return 0;
    }

    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];
    $amount = $entity->{$bundleFieldType . '_total'}->value;
    if ($reduce_balance) {
      $amount *= -1;
    }

    return \Drupal::service('se_business.service')->adjustBalance($business, (int) $amount);
  }

}
