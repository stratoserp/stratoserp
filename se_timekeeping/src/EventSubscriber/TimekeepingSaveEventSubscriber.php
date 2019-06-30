<?php

namespace Drupal\se_timekeeping\EventSubscriber;

use Drupal\comment\Entity\Comment;
use Drupal\hook_event_dispatcher\Event\Entity\EntityInsertEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\hook_event_dispatcher\Event\Entity\EntityUpdateEvent;
use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Drupal\se_core\ErpCore;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\node\Entity\Node;

class TimekeepingSaveEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    /** @noinspection PhpDuplicateArrayKeysInspection */
    return [
      HookEventDispatcherInterface::ENTITY_INSERT => 'timekeepingInsertMarkBilled',
      HookEventDispatcherInterface::ENTITY_UPDATE => 'timekeepingUpdateMarkBilled',
      HookEventDispatcherInterface::ENTITY_PRE_SAVE => 'timekeepingMarkNotBilled'
    ];
  }

  /**
   * When an invoice is saved, check if there are any timekeeping entries.
   * If there are, mark them as billed.
   *
   * @param EntityInsertEvent $event
   *
   */
  public function timekeepingInsertMarkBilled(EntityInsertEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity);
    }
  }

  /**
   * When an invoice is updated, check if there are any timekeeping entries.
   * If there are, mark them as billed.
   *
   * @param EntityUpdateEvent $event
   *
   */
  public function timekeepingUpdateMarkBilled(EntityUpdateEvent $event) {
    /** @var Node $entity */
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
   * @param EntityPresaveEvent $event
   *
   */
  public function timekeepingMarkNotBilled(EntityPresaveEvent $event) {
    /** @var Node $entity */
    $entity = $event->getEntity();
    if ($entity->getEntityTypeId() === 'node' && $entity->bundle() === 'se_invoice') {
      $this->timekeepingMarkItemsBilled($entity, FALSE);
    }
  }


  /**
   * Loop through the invoice entries and mark the originals as
   * billed/unbilled as dictated by the parameter.
   *
   * @param Node $entity
   * @param bool $billed
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function timekeepingMarkItemsBilled($entity, $billed = TRUE) {
    $bundle_field_type = 'field_' . ErpCore::ITEMS_BUNDLE_MAP[$entity->bundle()];

    foreach ($entity->{$bundle_field_type . '_items'} as $index => $item_line) {
      if ($item_line->target_type !== 'se_timekeeping') {
        continue;
      }
      if ($comment = Comment::load($item_line->target_id)) {
        $comment->set('field_tk_billed', $billed);
        $comment->save();
      }
    }
  }

}
