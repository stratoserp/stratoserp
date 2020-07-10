<?php

declare(strict_types=1);

namespace Drupal\se_item_line\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
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
  protected $configuration;
  protected $business;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_manager, ModuleHandlerInterface $module_handler, AccountInterface $current_user) {
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

    // Extract the business ref from the query
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
    foreach ($entities as $entity_id => $comment) {
      $output = [];
      $bundle = $comment->bundle();

      if ($item_code = $comment->se_tk_item->entity) {
        $output[] = $item_code->se_it_code->value;

        //$output[] = substr($item_code->label(), 0, 80);
        $output[] = substr($comment->label(), 0, 80);
        if (isset($item_code->se_it_sell_price->value)) {
          $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay($item_code->se_it_sell_price->value);
        }

        $options[$bundle][$entity_id] = implode(' ', $output);
      }
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

    $entity_type = $this->entityManager->getDefinition($this->target_type);

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      $matches = explode(' ', $match);
      foreach ($matches as $partial) {
        $key = Tags::encode($partial);
        $query->condition($label_key, $key, $match_operator);
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
