<?php

namespace Drupal\se_payments\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
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
    if (($entity = $event->getEntity())
      && ($entity->getEntityTypeId() !== 'node'
        || $entity->bundle() !== 'se_payment')) {
      return;
    }
    $total = 0;

    if (!array_key_exists($entity->bundle(), ErpCore::PAYMENTS_BUNDLE_MAP)) {
      return;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $items = $entity->{'field_' . ErpCore::PAYMENTS_BUNDLE_MAP[$entity->bundle()] . '_items'}->referencedEntities();

    foreach ($items as $ref_entity) {
      $total += $ref_entity->field_pa_amount->value;
    }

    /** @var \Drupal\node\Entity\Node $entity */
    $entity->{'field_' . ErpCore::PAYMENTS_BUNDLE_MAP[$entity->bundle()] . '_total'}->value = $total;
  }

}