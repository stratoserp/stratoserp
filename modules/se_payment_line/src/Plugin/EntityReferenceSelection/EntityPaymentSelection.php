<?php

declare(strict_types=1);

namespace Drupal\se_payment_line\Plugin\EntityReferenceSelection;

use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;

/**
 * Plugin implementation of the 'selection' entity_reference.
 *
 * @EntityReferenceSelection(
 *   id = "se_payment",
 *   label = @Translation("Payment: Select open invoices by customer"),
 *   group = "se_payment",
 *   weight = 1
 * )
 */
class EntityPaymentSelection extends DefaultSelection {

  /**
   * Target type.
   *
   * @var string
   */
  protected string $targetType;

  /**
   * Configuration.
   *
   * @var array
   */
  protected $configuration;

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    $this->targetType = $this->getConfiguration()['target_type'];

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
    $entities = $this->entityTypeManager->getStorage($this->targetType)->loadMultiple($result);

    /** @var \Drupal\se_invoice\Entity\Invoice $invoice */
    foreach ($entities as $entityId => $invoice) {
      $output = [];

      $output[] = 'in-' . $invoice->id();
      $output[] = $invoice->getName();
      $output[] = \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $invoice->getTotal());

      // Format - NameCode #Serial# Desc - Price.
      $options['se_invoice'][$entityId] = implode(' ', $output);
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
    $query->accessCheck(TRUE);
    $entity_type = $this->entityTypeManager->getDefinition($this->targetType);

    if (isset($match) && $label_key = $entity_type->getKey('label')) {
      foreach (explode(' ', $match) as $partial) {
        $key = Tags::encode($partial);
        $conditionGroup = $query->orConditionGroup()
          ->condition($label_key, $key, $match_operator)
          ->condition('id', $key, $match_operator);
        $query->condition($conditionGroup);
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
      $this->targetType = $this->getConfiguration()['target_type'];
      $entityType = $this->entityTypeManager->getDefinition($this->targetType);
      $result = parent::buildEntityQuery()
        ->condition($entityType->getKey('id'), $ids, 'IN')
        ->execute();
    }

    return $result;
  }

}
