<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\se_core\Event\SeCoreEvent;
use Drupal\se_core\Event\SeCoreEvents;
use Drupal\se_xero\Service\SeXeroContactService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SeCustomerInsert implements EventSubscriberInterface {

  /**
   * Xero contact lookup service.
   *
   * @var SeXeroContactService;
   */
  protected $contactService;

  /**
   * Constructor.
   *
   * @param SeXeroContactService $contact_service
   */
  public function __construct(SeXeroContactService $contact_service) {
    $this->contactService = $contact_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(EventSubscriberInterface $event) {
    return new static(
      $event->get('se_xero.contact_service')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      SeCoreEvents::NODE_CREATED => 'onInsert',
      SeCoreEvents::NODE_UPDATED => 'onInsert',
    ];
  }

  /**
   * When a customer is inserted, create the same customer in Xero.
   *
   * @param SeCoreEvent $event
   *
   */
  public function onInsert(SeCoreEvent $event) {
    $node = $event->getNode();
    if ($node->bundle() == 'se_customer' && !$node->xero_syncing) {
      if ($result = $this->contactService->sync($node)) {
        $event->setNode($node);
      }
    }
  }

}
