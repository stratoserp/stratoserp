<?php

declare(strict_types=1);

namespace Drupal\se_item\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\Query\QueryInterface;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "se_service_item",
 *   label = @Translation("Item: Filter service items by customer"),
 *   group = "se_service_item",
 *   weight = 1
 * )
 */
class EntityServiceItemSelection extends DefaultSelection {

  /**
   * Flag to indicate whether we want to see all items.
   *
   * @var bool
   */
  protected bool $virtualOnly = FALSE;

  /**
   * Const for the character used to indicate virtual.
   */
  public const FILTER_VIRTUAL = '@';

  /**
   * Target type.
   *
   * @var string
   */
  protected string $targetType;

  /**
   * Target type.
   *
   * @var array
   */
  protected array $targetBundles;

  /**
   * Configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * List of filtering characters.
   *
   * @var array
   */
  protected array $filterCharacters = [
    'virtual' => EntityItemSelection::FILTER_VIRTUAL,
  ];

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $this->targetType = $this->getConfiguration()['target_type'];
    $this->targetBundles = $this->getConfiguration()['target_bundles'];

    $filters = [];

    if ($match !== NULL) {
      $filters = $this->extractFilters($match);
      $match = $this->removeFiltersFromMatch($filters, $this->filterCharacters, $match);
    }

    $query = $this->buildEntityQuery($match, $match_operator, $filters);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $result = $query->execute();

    if (empty($result)) {
      return [];
    }

    $options = [];
    $entities = $this->entityTypeManager->getStorage($this->targetType)->loadMultiple($result);
    foreach ($entities as $entityId => $item) {
      $output = [];
      $bundle = $item->bundle();

      $output[] = $item->se_code->value;

      if ($bundle === 'se_stock' && !$item->se_serial->isEmpty()) {
        $output[] = '#' . $item->se_serial->value . '#';
      }
      // $output[] = substr($item->label(), 0, 80);
      $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $item->se_sell_price->value);

      // Format - Code #Serial# Desc - Price.
      $options[$bundle][$entityId] = implode(' ', $output);
    }

    return $options;
  }

  /**
   * Builds an EntityQuery to get referencable entities.
   *
   * @param string|null $match
   *   Text to match the label against. Defaults to NULL.
   * @param string $match_operator
   *   The operation the matching should be done with. Defaults
   *   to "CONTAINS".
   * @param array $filters
   *   Array of filters to apply to the query.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The EntityQuery object with the basic conditions and sorting applied to
   *   it.
   */
  protected function buildEntityQuery($match = NULL, $match_operator = 'CONTAINS', array $filters = []) {
    // Call parent to build the base query. Do not provide the $match
    // parameter, because we want to implement our own logic and we can't
    // unset conditions.
    /** @var \Drupal\Core\Entity\Query\Sql\Query $query */
    $query = parent::buildEntityQuery(NULL, $match_operator);

    // Include virtual items, or not.
    if ($this->virtualOnly) {
      $query->notExists('se_serial');
    }

    $entity_type = $this->entityTypeManager->getDefinition($this->targetType);

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      foreach (explode(' ', $match) as $partial) {
        $key = Tags::encode($partial);
        $conditionGroup = $query->orConditionGroup()
          ->condition($label_key, $key, $match_operator)
          ->condition('se_serial', $key, 'CONTAINS');
        $query->condition($conditionGroup);
      }

      // Apply the filters supplied by the user.
      $query = $this->applyFilters($query, $filters, $label_key);
    }

    return $query;
  }

  /**
   * Extracts filters from the query.
   *
   * @param string|null $match
   *   (Optional) Text to match the label against. Defaults to NULL.
   *
   * @return array
   *   An array of filters to apply to the search.
   */
  protected function extractFilters($match = NULL) {
    $filters = [];
    foreach (explode(' ', $match) as $partial) {
      $first_char = substr($partial, 0, 1);
      switch ($first_char) {
        case self::FILTER_VIRTUAL:
          $filters['virtual'][0] = substr($partial, 1);
          $this->virtualOnly = TRUE;
          break;
      }
    }

    return $filters;
  }

  /**
   * Remove filters from the query.
   *
   * @param array $filters
   *   The filters found in the query, that sould be removed.
   * @param array $filterCharacters
   *   The filter character mapping.
   * @param string|null $match
   *   The query that we want to find matches for.
   *
   * @return string|null
   *   The cleaned query string, all filters removed.
   */
  protected function removeFiltersFromMatch(array $filters, array $filterCharacters, $match = NULL) {
    if ($match !== NULL) {
      foreach ($filters as $filterType => $typeFilters) {
        foreach ($typeFilters as $type_filter) {
          $replace = $filterCharacters[$filterType] . $type_filter;
          $match = str_replace($replace, '', $match);
        }
      }
      return trim($match);
    }
    return $match;
  }

  /**
   * Apply the filters the user entered to the selection query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The query object to add the filters to.
   * @param array $filters
   *   The array of filters to apply.
   * @param string $labelKey
   *   The field we apply the filters on.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The altered query object, filters applied.
   */
  protected function applyFilters(QueryInterface $query, array $filters, string $labelKey): QueryInterface {
    if (isset($filters['virtual'])) {
      foreach ($filters['virtual'] as $filter) {
        $query->condition($labelKey, $filter, 'CONTAINS');
      }
      return $query;
    }

    // Build up the conditions.
    $conditionGroup = $query->orConditionGroup();

    foreach ($this->targetBundles as $bundle) {
      switch ($bundle) {
        case 'se_assembly':
          $conditionGroup
            ->condition('type', 'se_assembly')
            ->condition('se_sold', FALSE);
          break;

        case 'se_recurring';
          $conditionGroup->condition('type', 'se_recurring');
          break;

        case 'se_stock':
          $conditionGroup
            ->condition('type', 'se_stock')
            ->condition('se_sold', FALSE);
          break;

        case 'se_service':
          $conditionGroup->condition('type', 'se_service');
          break;

      }
    }
    $query->condition($conditionGroup);

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
