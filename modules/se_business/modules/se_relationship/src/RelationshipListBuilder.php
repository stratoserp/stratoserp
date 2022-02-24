<?php

declare(strict_types=1);

namespace Drupal\se_relationship;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Relationship entities.
 *
 * @ingroup se_relationship
 */
class RelationshipListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Relationship ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_relationship\Entity\Relationship $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_relationship.edit_form',
      ['se_relationship' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
