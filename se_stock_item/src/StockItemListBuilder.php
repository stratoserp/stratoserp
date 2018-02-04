<?php

namespace Drupal\se_stock_item;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Stock item entities.
 *
 * @ingroup se_stock_item
 */
class StockItemListBuilder extends EntityListBuilder {


  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Stock item ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\se_stock_item\Entity\StockItem */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_stock_item.edit_form',
      ['se_stock_item' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
