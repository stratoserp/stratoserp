<?php

declare(strict_types=1);

namespace Drupal\se_payment\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityDeleteEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\se_payment\Entity\Payment;
use Drupal\se_payment\Service\PaymentServiceInterface;

/**
 * Class PaymentSaveEventSubscriber.
 *
 * For each invoice in the payment, mark it as paid.
 *
 * @see \Drupal\se_invoice\EventSubscriber\InvoiceEventSubscriber
 *
 * @package Drupal\se_payment\EventSubscriber
 */
class PaymentEventSubscriber implements PaymentEventSubscriberInterface {

  /** @var \Drupal\se_payment\Service\PaymentServiceInterface */
  protected PaymentServiceInterface $paymentService;

  public function __construct(PaymentServiceInterface $paymentService) {
    $this->paymentService = $paymentService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_INSERT => ['paymentInsert', -25],
      EntityHookEvents::ENTITY_UPDATE => ['paymentUpdate', -25],
      EntityHookEvents::ENTITY_DELETE => ['paymentDelete', -25],
    ];
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

    $this->paymentService->updateInvoices($payment);
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

    $this->paymentService->updateInvoices($payment);
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

    $this->paymentService->updateInvoices($payment);
  }

}
