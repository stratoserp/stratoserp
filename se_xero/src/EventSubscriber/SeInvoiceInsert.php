<?php

namespace Drupal\se_xero\EventSubscriber;

use Drupal\se_core\Event\SeCoreEvent;
use Drupal\se_core\Event\SeCoreEvents;
use Drupal\se_xero\Service\SeXeroInvoiceService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SeInvoiceInsert implements EventSubscriberInterface {

  /**
   * Xero invoice lookup service.
   *
   * @var SeXeroInvoiceService;
   */
  protected $invoiceService;

  /**
   * Constructor.
   *
   * @param SeXeroInvoiceService $invoice_service
   */
  public function __construct(SeXeroInvoiceService $invoice_service) {
    $this->invoiceService = $invoice_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(EventSubscriberInterface $event) {
    return new static(
      $event->get('se_xero.invoice_service')
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
   * When an item is saved, create an associated stock item.
   *
   * @param SeCoreEvent $event
   *
   */
  public function onInsert(SeCoreEvent $event) {
    $node = $event->getNode();
    if ($node->bundle() == 'se_invoice' && !$node->isSyncing()) {
      // Call Xero service, abuse? isSyncing to avoid ininite recursion.
      if ($result = $this->invoiceService->sync($node)) {
        $event->setNode($node);
      }
    }
  }

}
