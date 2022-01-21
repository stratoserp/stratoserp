<?php

declare(strict_types=1);

namespace Drupal\se_payment\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_payment\Entity\Payment;

/**
 * Class PaymentSaveEventSubscriber.
 *
 * For each invoice in the payment, mark it as paid.
 *
 * @see \Drupal\se_invoice\EventSubscriber\InvoiceSaveEventSubscriber
 *
 * @package Drupal\se_payment\EventSubscriber
 */
class PaymentSaveEventSubscriber implements PaymentSaveEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [];

//    return [
//      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentInsert',
//      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdate',
//      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentPresave',
//      HookEventDispatcherInterface::ENTITY_DELETE => 'paymentDelete',
//    ];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentPresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $event->getEntity();

    if (!$payment instanceof Payment || $payment->isNew()) {
      return;
    }

    $amount = \Drupal::service('se_payment.service')->updateInvoices($payment, FALSE);
    \Drupal::service('se_payment.service')->updateBusinessBalance($payment, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $event->getEntity();
    if (!$payment instanceof Payment) {
      return;
    }

    $amount = \Drupal::service('se_payment.service')->updateInvoices($payment);
    \Drupal::service('se_payment.service')->updateBusinessBalance($payment, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $event->getEntity();
    if (!$payment instanceof Payment) {
      return;
    }

    $amount = \Drupal::service('se_payment.service')->updateInvoices($payment);
    \Drupal::service('se_payment.service')->updateBusinessBalance($payment, $amount);
  }

  /**
   * {@inheritdoc}
   */
  public function paymentDelete(EntityDeleteEvent $event): void {
    /** @var \Drupal\se_payment\Entity\Payment $payment */
    $payment = $event->getEntity();
    if (!$payment instanceof Payment) {
      return;
    }

    $amount = \Drupal::service('se_payment.service')->updateInvoices($payment, FALSE);
    \Drupal::service('se_payment.service')->updateBusinessBalance($payment, $amount);
  }

}
