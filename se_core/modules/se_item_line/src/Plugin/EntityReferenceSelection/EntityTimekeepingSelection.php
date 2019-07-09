<?php

namespace Drupal\se_item_line\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Html;
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
 *   id = "se_timekeeping",
 *   label = @Translation("Timekeeping: Filter timekeeping entries by customer"),
 *   group = "se_timekeeping",
 *   weight = 1
 * )
 */
class EntityTimekeepingSelection extends DefaultSelection {

  protected $target_type;
  protected $target_bundles;
  protected $configuration;

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
    $this->configuration = $this->getConfiguration();
    $this->target_type = $this->configuration['target_type'];
    $this->target_bundles = $this->configuration['target_bundles'];
    $parameters = \Drupal::request()->query->all();
    $this->business = $parameters['field_bu_ref'];

    $filters = [];

    $query = $this->buildEntityQuery($match, $match_operator, $filters);
    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $query_string = $query->__toString();
    \Drupal::logger('se_timekeeping')->info($query_string);
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

      // Format - Code #Serial# Desc - Price
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
    /** @var \Drupal\Core\Entity\Query\Sql\Query  $query */
    $query = parent::buildEntityQuery(NULL, $match_operator);

    $entity_type = $this->entityManager->getDefinition($this->target_type);

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      $matches = explode(' ', $match);
      foreach ($matches as $partial) {
        $query->condition($label_key, $partial, $match_operator);
      }
    }

    $query->condition('field_bu_ref', $this->business);

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
