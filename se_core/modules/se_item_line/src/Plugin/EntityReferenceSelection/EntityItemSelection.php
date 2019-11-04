<?php

namespace Drupal\se_item_line\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "se_item",
 *   label = @Translation("Item: Filter items by code and serial"),
 *   group = "se_item",
 *   weight = 1
 * )
 */
class EntityItemSelection extends DefaultSelection {

  protected $virtual_only = FALSE;
  protected $target_type;
  protected $configuration;

  public const FILTER_VIRTUAL = '@';

  protected $filterCharacters = [
    'virtual' => EntityItemSelection::FILTER_VIRTUAL,
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_manager, $module_handler, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('module_handler'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $configuration = $this->getConfiguration();
    $this->target_type = $configuration['target_type'];

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
    $entities = $this->entityManager->getStorage($this->target_type)->loadMultiple($result);
    foreach ($entities as $entity_id => $item) {
      $output = [];
      $bundle = $item->bundle();

      $output[] = $item->field_it_code->value;

      if ($bundle === 'se_stock' && !$item->field_it_serial->isEmpty()) {
        $output[] = '#' . $item->field_it_serial->value . '#';
      }
      // $output[] = substr($item->label(), 0, 80);
      $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay($item->field_it_sell_price->value);

      // Format - Code #Serial# Desc - Price.
      $options[$bundle][$entity_id] = implode(' ', $output);
    }

    return $options;
  }

  /**
   * Builds an EntityQuery to get referenceable entities.
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
    if ($this->virtual_only) {
      $query->notExists('field_it_serial');
    }

    $entity_type = $this->entityManager->getDefinition($this->target_type);

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      $matches = explode(' ', $match);
      foreach ($matches as $partial) {
        $key = Tags::encode($partial);
        $query->condition($label_key, $key, $match_operator);
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
    $matches = explode(' ', $match);

    foreach ($matches as $partial) {
      $first_char = substr($partial, 0, 1);
      switch ($first_char) {
        case EntityItemSelection::FILTER_VIRTUAL:
          $filters['virtual'][0] = substr($partial, 1);
          $this->virtual_only = TRUE;
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
   * @param array $filter_characters
   *   The filter character mapping.
   * @param string|null $match
   *   The query that we want to find matches for.
   *
   * @return string|null
   *   The cleaned query string, all filters removed.
   */
  protected function removeFiltersFromMatch(array $filters, array $filter_characters, $match = NULL) {
    if ($match != NULL) {
      foreach ($filters as $filter_type => $type_filters) {
        foreach ($type_filters as $type_filter) {
          $replace = $filter_characters[$filter_type] . $type_filter;
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
   * @param string $label_key
   *   The field we apply the filters on.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The altered query object, filters applied.
   */
  protected function applyFilters(QueryInterface $query, array $filters, $label_key) {
    if (isset($filters['virtual'])) {
      foreach ($filters['virtual'] as $filter) {
        $query->condition($label_key, $filter, 'CONTAINS');
      }
    }

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    $result = [];
    if ($ids) {
      $this->target_type = $this->configuration['target_type'];
      $entity_type = $this->entityManager->getDefinition($this->target_type);
      $query = parent::buildEntityQuery();
      $result = $query
        ->condition($entity_type->getKey('id'), $ids, 'IN')
        ->execute();
    }

    return $result;
  }

}
