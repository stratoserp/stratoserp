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
  protected $configuration;

  /**
   * The customer reference id.
   *
   * @var int
   */
  protected int $customer;

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $matchOperator = 'CONTAINS', $limit = 0) {
    $this->targetType = $this->getConfiguration()['target_type'];

    // Extract the customer ref from the query.
    $parameters = \Drupal::request()->query;
    if ($parameters->has('se_cu_ref')) {
      $this->customer = (int) $parameters->get('se_cu_ref');
    }

    // Extract the customer ref from the ajax request?
    $parameters = \Drupal::request()->request;
    if (empty($this->customer) && (int) $parameters->has('se_cu_ref')) {
      $matches = [];
      $customer = $parameters->get('se_cu_ref');
      if (preg_match("/.+\s\(([^\)]+)\)/", $customer[0]['target_id'], $matches)) {
        $this->customer = (int) $matches[1];
      }
    }

    $query = $this->buildEntityQuery($match, $matchOperator);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage($this->targetType)->loadMultiple($result);
    foreach ($entities as $entityId => $timekeeping) {
      $output = [];
      $bundle = $timekeeping->bundle();

      if ($itemCode = $timekeeping->se_it_ref->entity) {
        $output[] = $itemCode->se_code->value;

        $output[] = substr($timekeeping->label(), 0, 80);
        if (isset($itemCode->se_sell_price->value)) {
          $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $itemCode->se_sell_price->value);
        }

        $options[$bundle][$entityId] = implode(' ', $output);
      }
    }

    return $options;
  }

  /**
   * Builds an EntityQuery to get referencable entities.
   *
   * @param string|null $match
   *   Text to match the label against. Defaults to NULL.
   * @param string $matchOperator
   *   The operation the matching should be done with. Defaults
   *   to "CONTAINS".
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The EntityQuery object with the basic conditions and sorting applied to
   *   it.
   */
  protected function buildEntityQuery($match = NULL, $matchOperator = 'CONTAINS') {
    // Call parent to build the base query. Do not provide the $match
    // parameter, because we want to implement our own logic and we can't
    // unset conditions.
    /** @var \Drupal\Core\Entity\Query\Sql\Query $query */
    $query = parent::buildEntityQuery(NULL, $matchOperator);

    $entityType = $this->entityTypeManager->getDefinition($this->targetType);

    if (isset($match) && $labelKey = $entityType->getKey('label')) {
      foreach (explode(' ', $match) as $partial) {
        $key = Tags::encode($partial);
        $query->condition($labelKey, $key, $matchOperator);
      }
    }

    $query->condition('se_cu_ref', $this->customer);

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    $result = [];
    if ($ids) {
      $this->targetType = $this->getConfiguration()['target_type'];
      $entityType = $this->entityTypeManager->getDefinition($this->targetType);
      $result = parent::buildEntityQuery()
        ->condition($entityType->getKey('id'), $ids, 'IN')
        ->execute();
    }

    return $result;
  }

}
