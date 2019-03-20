<?php

namespace Drupal\se_payments\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentsPresave implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentsPreSave',
    ];
  }

  /**
   * @param EntityPresaveEvent $event
   *
   */
  public function paymentsPreSave(EntityPresaveEvent $event) {
    $node = $event->getEntity();
    $total = 0;

    $bundles = [
      'se_payment' => 'pa',
    ];

    if (!array_key_exists($node->bundle(), $bundles)) {
      return;
    }

    /** @var \Drupal\node\Entity\Node $node */
    $items = $node->{'field_' . $bundles[$node->bundle()] . '_items'}->referencedEntities();

    foreach ($items as $ref_entity) {
      $total += $ref_entity->field_pa_amount->value;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $node->{'field_' . $bundles[$node->bundle()] . '_total'}->value = $total;
  }

}