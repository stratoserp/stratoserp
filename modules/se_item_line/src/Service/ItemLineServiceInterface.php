<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Service;

use Drupal\Core\Entity\EntityInterface;
use Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface;

/**
 * Item line service class for common item line manipulations.
 */
interface ItemLineServiceInterface {

  /**
   * Calculate the total and more of an entity with item lines.
   *
   * @param \Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface $entity
   *   Entity to update the totals for.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The updated entity.
   */
  public function calculateTotal(StratosLinesEntityBaseInterface $entity): EntityInterface;

  /**
   * Assign serial values.
   *
   * @param \Drupal\stratoserp\Entity\StratosLinesEntityBaseInterface $entity
   *   Entity to update the serial numbers for.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The updated entity.
   */
  public function setSerialValues(StratosLinesEntityBaseInterface $entity): EntityInterface;

}
