<?php

declare(strict_types=1);

namespace Drupal\se_timekeeping;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Timekeeping entities.
 *
 * @ingroup se_timekeeping
 */
class TimekeepingListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Timekeeping ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_timekeeping\Entity\Timekeeping $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_timekeeping.edit_form',
      ['se_timekeeping' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
