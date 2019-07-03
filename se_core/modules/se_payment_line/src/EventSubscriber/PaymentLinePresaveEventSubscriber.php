<?php

namespace Drupal\se_payment_line\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentLinePresaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentLineNodePresave',
    ];
  }

  /**
   * When saving a payment, calculate the total of the items for saving.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function paymentLineNodePresave(EntityPresaveEvent $event) {
    if (($entity = $event->getEntity()) && ($entity->getEntityTypeId() !== 'node')) {
      return;
    }

    // Check that its a node type which has items.
    if (!array_key_exists($entity->bundle(), ErpCore::PAYMENTS_BUNDLE_MAP)) {
      return;
    }

    $total = 0;
    $bundle_field_type = 'field_' . ErpCore::PAYMENTS_BUNDLE_MAP[$entity->bundle()];

    // Loop through the payment lines, adjusting price
    // for storage and calculating total
    $payment_lines = [];
    foreach ($entity->{$bundle_field_type . '_items'} as $index => $payment_line) {
      // Finally update the line and add it to the list
      $payment_lines[] = $payment_line;
      $total += $payment_line['amount'];
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $entity->{$bundle_field_type . '_lines'} = $payment_lines;
    $entity->{$bundle_field_type . '_total'}->value = $total;
  }

}
