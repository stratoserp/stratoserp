<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\comment\Entity\Comment;
use Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\node\Entity\Node;
use Drupal\stratoserp\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TimekeepingSaveEventSubscriber.
 *
 * Mark timekeeping entries status if they are included on an invoice.
 *
 * @package Drupal\se_timekeeping\EventSubscriber
 */
class TimekeepingInvoiceEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'timekeepingInvoiceInsert',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'timekeepingInvoiceUpdate',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'timekeepingInvoicePresave',
    ];
  }

  /**
   * When an invoice is saved, check if there are any timekeeping entries.
   *
   * When there are, mark them as billed.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *   The Event to handle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoiceInsert(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * When an invoice is updated, check if there are any timekeeping entries.
   *
   * When there are, mark them as billed.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *   The Event to handle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoiceUpdate(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * Mark timekeeping entries as unbilled before saving.
   *
   * This needs to be done in case they have been removed from the invoice,
   * and will need to be available to be billed again.
   *
   * @param \Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *   The Event to handle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInvoicePresave(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity, FALSE);
    }
  }

  /**
   * Loop through the invoice entries and mark the originals as required.
   *
   * @param \Drupal\node\Entity\Node $entity
   *   The entity to update timekeeping items.
   * @param bool $billed
   *   Marking billed or not billed?
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function timekeepingMarkItemsBilled(Node $entity, $billed = TRUE): void {
    $bundleFieldType = 'se_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    foreach ($entity->{$bundleFieldType . '_lines'} as $itemLine) {
      if ($itemLine->target_type === 'comment') {
        /** @var \Drupal\comment\Entity\Comment $comment */
        if ($comment = Comment::load($itemLine->target_id)) {
          // @todo Make a service for this?
          if ($comment->se_tk_billed !== $billed) {
            $comment->set('se_tk_billed', $billed);
            if ($billed) {
              $comment->set('se_tk_invoice_ref', $entity->id());
            }
            else {
              $comment->set('se_tk_invoice_ref', NULL);
            }
            $comment->save();
          }
        }
      }
    }
  }

}
