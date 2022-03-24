<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Traits;

use Drupal\se_customer\Entity\Customer;

/**
 * Trait to provide some common things for our entities.
 *
 * Can't do this via a base class as the entities would inherit from themselves.
 *
 * @package Drupal\stratoserp\Traits
 */
trait EntityTrait {

  /**
   * {@inheritdoc}
   */
  public function getCustomer(): ?Customer {
    $customerId = $this->se_cu_ref->entity->id();
    return Customer::load($customerId);
  }

  /**
   * {@inheritdoc}
   *
   * @todo Needs love.
   */
  public function generateFilename(): string {
    $name_parts = [
      $this->getCustomer()->getName(),
      $this->getEntityType()->getLabel(),
      date('Y-m-d', (int) $this->getCreatedTime()),
      sprintf("%06d", $this->id()),
    ];

    return implode('_', $name_parts);
  }

}
