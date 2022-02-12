<?php

declare(strict_types=1);

namespace Drupal\se_goods_receipt;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Goods receipt entities.
 *
 * @ingroup se_goods_receipt
 */
class GoodsReceiptListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Goods receipt ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_goods_receipt\Entity\GoodsReceipt $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_goods_receipt.edit_form',
      ['se_goods_receipt' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
