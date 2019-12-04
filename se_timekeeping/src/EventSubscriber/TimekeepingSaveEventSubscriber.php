<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\comment\Entity\Comment;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class TimekeepingSaveEventSubscriber.
 *
 * Mark timekeeping entries status if they are included on an invoice.
 *
 * @package Drupal\se_timekeeping\EventSubscriber
 */
class TimekeepingSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'timekeepingInsertMarkBilled',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'timekeepingUpdateMarkBilled',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'timekeepingMarkNotBilled',
    ];
  }

  /**
   * When an invoice is saved, check if there are any timekeeping entries.
   * If there are, mark them as billed.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent $event
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingInsertMarkBilled(EntityInsertEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * When an invoice is updated, check if there are any timekeeping entries.
   * If there are, mark them as billed.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent $event
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingUpdateMarkBilled(EntityUpdateEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * When an invoice is about to be saved, mark any timekeeping entries in
   * its pre-saved state as unbilled in case they have been removed from the
   * invoice.
   *
   * @param \Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent $event
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function timekeepingMarkNotBilled(EntityPresaveEvent $event): void {
    /** @var \Drupal\node\Entity\Node $entity */
    $entity = $event->getEntity();

    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity, FALSE);
    }
  }

  /**
   * Loop through the invoice entries and mark the originals as
   * billed/un-billed as dictated by the parameter.
   *
   * @param \Drupal\node\Entity\Node $entity
   * @param bool $billed
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function timekeepingMarkItemsBilled($entity, $billed = TRUE): void {
    $bundle_field_type = 'field_' . ErpCore::ITEM_LINE_NODE_BUNDLE_MAP[$entity->bundle()];

    foreach ($entity->{$bundle_field_type . '_lines'} as $index => $item_line) {
      if ($item_line->target_type === 'comment') {
        /** @var \Drupal\comment\Entity\Comment $comment */
        if ($comment = Comment::load($item_line->target_id)) {
          // TODO - Make a service for this?
          if ($comment->field_tk_billed != $billed) {
            $comment->field_tk_billed->value = $billed;
            if ($billed) {
              $comment->field_tk_invoice_ref->value = $entity->id();
            }
            else {
              unset($comment->field_tk_invoice_ref->value);
            }
            $comment->save();
          }
        }
      }
    }
  }

}
