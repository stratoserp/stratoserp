<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Our extensions to the event subscriber for Payment line saving.
 *
 * @package Drupal\se_payment_line\EventSubscriber
 */
interface PaymentLineEventSubscriberInterface extends EventSubscriberInterface {

  /**
   * When saving a payment, calculate the total of the items for saving.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   *
   * @see \Drupal\se_payment\Entity\Payment
   */
  public function paymentLineEntityPresave(EntityPresaveEvent $event);

}
