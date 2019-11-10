<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PaymentLinePresaveEventSubscriber.
 *
 * When a node with item lines is saved, recalculate the total of the node.
 *
 * @package Drupal\se_payment_line\EventSubscriber
 */
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
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   */
  public function paymentLineNodePresave(EntityPresaveEvent $event) {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() !== 'node') {
      return;
    }

    // Check that its a node type which has items.
    if (!array_key_exists($entity->bundle(), ErpCore::PAYMENT_LINE_NODE_BUNDLE_MAP)) {
      return;
    }

    $total = 0;
    $bundle_field_type = 'field_' . ErpCore::PAYMENT_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    // Loop through the payment lines to calculate total.
    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $payment_line) {
      $total += $payment_line->amount;
    }

    $entity->{$bundle_field_type . '_total'}->value = $total;
  }

}
