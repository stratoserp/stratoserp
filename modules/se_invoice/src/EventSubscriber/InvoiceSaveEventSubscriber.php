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
class InvoiceSaveEventSubscriber implements InvoiceSaveEventSubscriberInterface {

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
   * {@inheritdoc}
   */
  public function invoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'se_invoice') {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    $this->increaseBusinessBalance($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'se_invoice') {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    $this->increaseBusinessBalance($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function invoiceAdjust(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() !== 'se_invoice'
      || $entity->isNew()) {
      return;
    }

    if ($this->isSkipInvoiceSaveEvents($entity)) {
      return;
    }

    // Is this the right way?
    $this->reduceBusinessBalance($entity);
  }

  /**
   * On invoicing, increase the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The event we are working with.
   *
   * @return void|int
   *   The new balance is returned.
   */
  private function increaseBusinessBalance(EntityInterface $entity): int {
    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($entity)) {
      \Drupal::logger('se_business_invoice_save')->error('No business set for %entity', ['%entity' => $entity->id()]);
      return 0;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$entity->bundle()];
    $amount = $entity->{$bundleFieldType . '_total'}->value;

    return \Drupal::service('se_business.service')->adjustBalance($business, (int) $amount);
  }

  /**
   * On invoicing, reduce the business balance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The event we are working with.
   *
   * @return void|int
   *   The new balance is returned.
   */
  private function reduceBusinessBalance(EntityInterface $entity): int {
    if (!$business = \Drupal::service('se_business.service')->lookupBusiness($entity)) {
      \Drupal::logger('se_business_invoice_save')->error('No business set for %entity', ['%entity' => $entity->id()]);
      return 0;
    }

    $bundleFieldType = 'se_' . ErpCore::SE_ITEM_LINE_BUNDLES[$entity->bundle()];
    $amount = $entity->{$bundleFieldType . '_total'}->value;
    $amount *= -1;

    return \Drupal::service('se_business.service')->adjustBalance($business, (int) $amount);
  }

}
