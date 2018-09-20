<?php

namespace Drupal\se_stock_item\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Component\Utility\Html;

/**
 * Entity reference selection.
 *
 * @EntityReferenceSelection(
 *   id = "se_stock_item",
 *   label = @Translation("Stock item selection"),
 *   group = "se_stock_item",
 *   weight = 0
 * )
 */
class StockItemSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS') {
    $in_stock = TRUE;
    $supplier_node = FALSE;

    // An '@' at the front means in stock or not.
    if (strpos($match, '@') === 0) {
      $in_stock = FALSE;
      $match = ltrim($match, '@');
    }

    if (strpos($match, ':')) {
      list($supplier, $item_string) = explode(':', $match);
      $supplier_node = \Drupal::entityQuery('node')
        ->condition('type', 'se_supplier')
        ->condition('title', $supplier)
        ->execute();
      $match = $item_string;
    }

//    $query = \Drupal::entityQuery('node')
//      ->condition('type', 'se_item')
//      ->condition('field_it_code', "%" . $match . "%", 'LIKE')
//      ->condition('status', 1)
//      ->range(0, 10);
//
//    if ($supplier_node) {
//      $query->condition('field_it_supplier_ref', $supplier_node->id());
//    }

//    $or_condition = $query->orConditionGroup()
//      ->condition('title', $feed->label())
//      ->condition('url', $feed->getUrl());
//    $duplicates = $query
//      ->condition($or_condition)
//      ->execute();

//    if ($in_stock) {
//      // Left join in the stock
//    }

//    $view = views_

    $nids = $query->execute();

    return $nids;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $target_type = $this->getConfiguration()['target_type'];

    $result = $this->buildEntityQuery($match, $match_operator);

    $options = [];
    $entities = $this->entityManager->getStorage($target_type)->loadMultiple($result);
    foreach ($entities as $entity_id => $entity) {
      $bundle = $entity->bundle();
      $options[$bundle][$entity_id] = Html::escape($this->entityManager->getTranslationFromContext($entity)->label());
      // $entity->field_si_serial->value
      // $entity->field_si_code->value
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    $result = [];
    if ($ids) {
      $temp = 1;
//      $target_type = $this->configuration['target_type'];
//      $entity_type = $this->entityManager->getDefinition($target_type);
//      $query = $this->buildEntityQuery();
//      $result = $query
//        ->condition($entity_type->getKey('id'), $ids, 'IN')
//        ->execute();
    }

    return $result;
  }

}
