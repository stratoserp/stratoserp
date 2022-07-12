<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Interface for base functions used in the various entities.
 */
interface StratosEntityBaseInterface extends ContentEntityInterface, RevisionLogInterface, EntityChangedInterface, EntityOwnerInterface {

  /**
   * Gets the Entity name.
   *
   * @return string
   *   Name of the Entity.
   */
  public function getName();

  /**
   * Sets the Entity name.
   *
   * @param string $name
   *   The Entity name.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called Entity.
   */
  public function setName($name);

  /**
   * Get the Entity creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Entity.
   */
  public function getCreatedTime();

  /**
   * Sets the creation timestamp.
   *
   * @param int $timestamp
   *   The Entity creation timestamp.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The called Entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Return the search prefix.
   */
  public function getSearchPrefix(): string;

  /**
   * Generate a PDF of the entity and return the path to if.
   *
   * @return string
   *   Path to the generated pdf.
   */
  public function generatePdf(): string;

}
