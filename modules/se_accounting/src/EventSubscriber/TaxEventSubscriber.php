<?php

declare(strict_types=1);

namespace Drupal\se_accounting\EventSubscriber;

use Drupal\core_event_dispatcher\EntityHookEvents;
use Drupal\core_event_dispatcher\Event\Entity\EntityPresaveEvent;
use Drupal\se_accounting\Service\TaxAmountServiceInterface;
use Drupal\stratoserp\Constants;

/**
 * Implement an event subscriber to calculate and store tax.
 */
class TaxEventSubscriber implements TaxEventSubscriberInterface {

  /**
   * @var \Drupal\se_accounting\Service\TaxAmountServiceInterface
   *  Storage for the tax amount service.
   */
  protected TaxAmountServiceInterface $taxAmountService;

  /**
   * Simple constructor.
   */
  public function __construct(TaxAmountServiceInterface $taxAmountService) {
    $this->taxAmountService = $taxAmountService;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      EntityHookEvents::ENTITY_PRE_SAVE => ['taxPreAction', 25],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function taxPreAction(EntityPresaveEvent $event): void {
    /** @var \Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface $entity */
    $entity = $event->getEntity();

    // Only work on entities that need tax calculated.
    if (!in_array($entity->bundle(), Constants::SE_TAX_ENTITIES)) {
      return;
    }

    $tax = $this->taxAmountService->calculateTax($entity->getTotal());

    $entity->se_tax->value = $tax;
  }

}
