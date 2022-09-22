<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\se_payment\Entity\Payment;

/**
 * Class PaymentLinePresaveEventSubscriber.
 *
 * When a payment with item lines is saved, re-calc the total of the payment.
 *
 * @package Drupal\se_payment_line\EventSubscriber
 */
class PaymentLineEventSubscriber implements PaymentLineEventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => 'paymentLineEntityPresave',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function paymentLineEntityPresave(EntityPresaveEvent $event): void {
    $payment = $event->getEntity();
    if (!$payment instanceof Payment) {
      return;
    }

    \Drupal::service('se_payment_line.service')->calculateTotal($payment);
  }

}
