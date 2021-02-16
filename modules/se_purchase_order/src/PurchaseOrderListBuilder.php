<?php

namespace Drupal\se_purchase_order;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of PurchaseOrder entities.
 *
 * @ingroup se_purchase_order
 */
class PurchaseOrderListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('PurchaseOrder ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_purchase_order\Entity\PurchaseOrder $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_purchase_order.edit_form',
      ['se_purchase_order' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
