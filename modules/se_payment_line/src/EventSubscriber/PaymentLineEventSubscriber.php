<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\EventSubscriber;

use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\stratoserp\Constants;

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
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentLineEntityPresave',
    ];
  }

  /**
   * When saving a payment, calculate the total of the items for saving.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The event we are working with.
   */
  public function paymentLineEntityPresave(EntityPresaveEvent $event): void {
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'se_payment') {
      return;
    }

    // Check that its an entity type which has items.
    if (!array_key_exists($entity->getEntityTypeId(), Constants::SE_PAYMENT_LINE_BUNDLES)) {
      return;
    }

    $total = 0;

    // Loop through the payment lines to calculate total.
    foreach ($entity->se_payment_lines as $paymentLine) {
      $total += $paymentLine->amount;
    }

    $entity->se_total->value = $total;
  }

}
