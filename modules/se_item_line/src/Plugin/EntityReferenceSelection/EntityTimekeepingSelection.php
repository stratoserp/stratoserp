<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "se_timekeeping",
 *   label = @Translation("Timekeeping: Filter timekeeping entries by customer"),
 *   group = "se_timekeeping",
 *   weight = 1
 * )
 */
class EntityTimekeepingSelection extends DefaultSelection {

  /**
   * The targeted type of timekeeping entry.
   *
   * @var string
   */
  protected string $targetType;

  /**
   * Configuration array.
   *
   * @var array
   */
  protected array $configuration;

  /**
   * The business reference id.
   *
   * @var int
   */
  protected int $business;

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $matchOperator = 'CONTAINS', $limit = 0) {
    $configuration = $this->getConfiguration();
    $this->targetType = $configuration['target_type'];

    // Extract the business ref from the query.
    $parameters = \Drupal::request()->query;
    if ($parameters->has('se_bu_ref')) {
      $this->business = $parameters->get('se_bu_ref');
    }

    // Extract the business ref from the ajax request?
    $parameters = \Drupal::request()->request;
    if (empty($this->business) && $parameters->has('se_bu_ref')) {
      $matches = [];
      $business = $parameters->get('se_bu_ref');
      if (preg_match("/.+\s\(([^\)]+)\)/", $business[0]['target_id'], $matches)) {
        $this->business = $matches[1];
      }
    }

    $filters = [];

    $query = $this->buildEntityQuery($match, $matchOperator, $filters);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityManager->getStorage($this->targetType)->loadMultiple($result);
    foreach ($entities as $entityId => $comment) {
      $output = [];
      $bundle = $comment->bundle();

      if ($itemCode = $comment->se_tk_item->entity) {
        $output[] = $itemCode->se_it_code->value;

        $output[] = substr($comment->label(), 0, 80);
        if (isset($itemCode->se_it_sell_price->value)) {
          $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $itemCode->se_it_sell_price->value);
        }

        $options[$bundle][$entityId] = implode(' ', $output);
      }
    }

    return $options;
  }

  /**
   * Builds an EntityQuery to get referenceable entities.
   *
   * @param string|null $match
   *   Text to match the label against. Defaults to NULL.
   * @param string $matchOperator
   *   The operation the matching should be done with. Defaults
   *   to "CONTAINS".
   * @param array $filters
   *   Array of filters to apply to the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The EntityQuery object with the basic conditions and sorting applied to
   *   it.
   */
  protected function buildEntityQuery($match = NULL, $matchOperator = 'CONTAINS', array $filters = []) {
    // Call parent to build the base query. Do not provide the $match
    // parameter, because we want to implement our own logic and we can't
    // unset conditions.
    /** @var \Drupal\Core\Entity\Query\Sql\Query $query */
    $query = parent::buildEntityQuery(NULL, $matchOperator);

    $entityType = $this->entityManager->getDefinition($this->targetType);

    if (isset($match) && $labelKey = $entityType->getKey('label')) {
      foreach (explode(' ', $match) as $partial) {
        $key = Tags::encode($partial);
        $query->condition($labelKey, $key, $matchOperator);
      }
    }

    $query->condition('se_bu_ref', $this->business);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    $result = [];
    if ($ids) {
      $this->targetType = $this->configuration['target_type'];
      $entityType = $this->entityManager->getDefinition($this->targetType);
      $result = parent::buildEntityQuery()
        ->condition($entityType->getKey('id'), $ids, 'IN')
        ->execute();
    }

    return $result;
  }

}
