<?php

declare(strict_types=1);

namespace Drupal\se_invoice;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of Invoice entities.
 *
 * @ingroup se_invoice
 */
class InvoiceListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Invoice ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\se_invoice\Entity\Invoice $entity */
    $row['id'] = $entity->id();
    $row['name'] = Link::createFromRoute(
      $entity->label(),
      'entity.se_invoice.edit_form',
      ['se_invoice' => $entity->id()]
    );
    return $row + parent::buildRow($entity);
  }

}
