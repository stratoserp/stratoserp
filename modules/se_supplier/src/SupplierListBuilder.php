<?php

declare(strict_types=1);

namespace Drupal\se_supplier;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Supplier entities.
 *
 * @ingroup se_supplier
 */
class SupplierListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Supplier ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_supplier\Entity\Supplier $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_supplier.edit_form',
      ['se_supplier' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
