<?php

namespace Drupal\se_stock\EventSubscriber;

use Drupal\se_core\Event\SeCoreEvent;
use Drupal\se_core\Event\SeCoreEvents;
use Drupal\se_item\Entity\Item;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Entity\EntityTypeManager;

class ItemInsert implements EventSubscriberInterface {
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   */
  public function __construct(EntityTypeManager $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SeCoreEvents::NODE_CREATED => 'onInsert',
    ];
  }

  /**
   * When an item is saved, create an associated stock item.
   *
   * @param SeCoreEvent $event
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function onInsert(SeCoreEvent $event) {
    $node = $event->getNode();
    if ($node->bundle() == 'se_stock') {
      $stock_item = Item::create([
        'type'    => 'se_stock',
        'user_id' => '1',
        'name'    => $node->title->value,
        'field_it_serial'    => ['value' => ''],
      ]);
      $stock_item->save();
    }
  }

}
