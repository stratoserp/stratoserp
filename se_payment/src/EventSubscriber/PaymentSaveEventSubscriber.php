<?php

namespace Drupal\se_payment\EventSubscriber;

use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

class PaymentSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'paymentInsertMarkPaid',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'paymentUpdateMarkPaid',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'paymentMarkUnPaid'
    ];
  }

  /**
   * When a payment is saved, mark all invoices listed as paid.
   *
   * @param EntityInsertEvent $event
   *
   */
  public function paymentInsertMarkPaid(EntityInsertEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->paymentMarkLineStatus($entity);
    }
  }

  /**
   * Whn a payment is updated, make all invoices as paid.
   *
   * @param EntityUpdateEvent $event
   */
  public function paymentUpdateMarkPaid(EntityUpdateEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->paymentMarkLineStatus($entity);
    }
  }

  /**
   * When a payment is about to be saved, mark any existing payment lines
   * in its pre-saved state as unpaid in case they have been removed from the
   * payment.
   *
   * @param EntityPresaveEvent $event
   *
   */
  public function paymentMarkUnPaid(EntityPresaveEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->paymentMarkLineStatus($entity, FALSE);
    }
  }

  /**
   * Loop through the payment entries and mark the invoices as
   * paid/unpaid as dictated by the parameter.
   *
   * @param Node $entity
   * @param bool $paid
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function paymentMarkLineStatus($entity, $paid = TRUE) {
    if ($paid) {
      $term = Term::load('closed');
    }
    else {
      $term = Term::load('open');
    }

    $bundle_field_type = 'field_' . ErpCore::PAYMENTS_BUNDLE_MAP[$entity->bundle()];

    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if ($invoice = Node::load($item_line->target_id)) {
        // TODO - Make a service for this?
        $invoice->set('field_status_ref', $term);
        $invoice->save();
      }
    }
  }

}