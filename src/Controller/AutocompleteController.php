<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityInterface;
use Drupal\se_customer\Entity\Customer;
use Drupal\se_information\Entity\Information;
use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\Constants;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * A custom autocomplete controller for the main search form.
 */
class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object to work with.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The response to send back.
   */
  public function handleAutocomplete(Request $request): JsonResponse {
    $matches = $regexMatches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $searchString = Tags::explode($input);
      $searchString = mb_strtolower(array_pop($searchString));

      // If a shorthand style, don't try anything else.
      if (preg_match("/(..)-(\d+)/", $searchString, $regexMatches)) {
        [, $type, $code] = $regexMatches;
        return $this->handlespecific($type, $code);
      }

      $matches = $this->findCustomers($searchString);
      foreach (Constants::SE_ENTITY_LOOKUP as $code => $type) {
        // Don't do customer again.
        if ($code === 'bu') {
          continue;
        }

        $matches += $this->findEntity(
          $type['type'],
          $type['label'],
          ['name', 'id'],
          $searchString);
      }
    }

    return new JsonResponse($matches);
  }

  /**
   * Handle advanced users specifying the shorthand style direct lookups.
   *
   * @param string $type
   *   The shorthand code for an entity type.
   * @param string $code
   *   The entity id/code.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The Json response.
   */
  private function handleSpecific(string $type, string $code): JsonResponse {
    $matches = [];

    $fullType = Constants::SE_ENTITY_LOOKUP[$type];
    if ($entity = \Drupal::entityTypeManager()->getStorage($fullType['type'])->load($code)) {
      $matches[] = $this->buildEntityOutput($fullType['label'], (int) $entity->id(), $entity);
    }

    return new JsonResponse($matches);
  }

  /**
   * Return entities of $type with $text in $field, prefix with $description.
   *
   * @param string $type
   *   The type of node to search for.
   * @param string $description
   *   The text description for the type.
   * @param array $fields
   *   The field to be searched.
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  private function findEntity(string $type, string $description, array $fields, string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery($type)
      ->accessCheck(TRUE)
      ->range(0, 10);

    $conditionGroup = $query->orConditionGroup();
    foreach ($fields as $field) {
      $conditionGroup->condition($field, '%' . $text . '%', 'LIKE');
    }
    $query->condition($conditionGroup);

    $entityIds = $query->execute();

    $entities = \Drupal::entityTypeManager()->getStorage($type)->loadMultiple($entityIds);
    foreach ($entities as $entityId => $entity) {
      $matches[] = $this->buildEntityOutput($description, (int) $entityId, $entity);
    }

    return $matches;
  }

  /**
   * Build an output string for the various entity types.
   *
   * @param string $description
   *   The description of the entity type.
   * @param int $entityId
   *   The entity id we'd working with.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Could be any one of many.
   *
   * @return array
   *   Formatted array for matches.
   */
  #[ArrayShape([
    'value' => "string",
    'label' => "string",
  ])]
  private function buildEntityOutput(string $description, int $entityId, EntityInterface $entity) {
    $key = sprintf("%s (%s-%s)", $entity->getName(), $entity->getSearchPrefix(), $entityId);
    $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
    // Names containing commas or quotes must be wrapped in quotes.
    $key = Tags::encode($key);
    $fields = array_filter([
      $description,
      $key,
    ]);
    $outputDescription = implode(' - ', $fields);

    return [
      'value' => $key,
      'label' => $outputDescription,
    ];
  }

  /**
   * Return customer matching text.
   *
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  #[ArrayShape([
    'value' => "string",
    'label' => "string",
  ])]
  private function findCustomers(string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery('se_customer')
      ->accessCheck(TRUE)
      ->condition('name', '%' . $text . '%', 'LIKE')
      ->condition('se_status', TRUE)
      ->range(0, 10);

    $itemIds = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Customer::loadMultiple($itemIds) as $entityId => $customer) {
      $fields = array_filter([
        $customer->getName(),
      ]);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $outputDescription = $customer->getName();

      $key .= ' (' . $entityId . ')';
      $customerId = $customer->id();
      $matches[] = [
        'value' => $key,
        'label' => $outputDescription . " - ($customerId)",
      ];
    }

    return $matches;
  }

  /**
   * Return items of $type with $text in $field, prefix with $description.
   *
   * @param string $description
   *   The text description for the type.
   * @param string $field
   *   The field to be searched.
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  #[ArrayShape([
    'value' => "string",
    'label' => "string",
  ])]
  private function findItems(string $description, string $field, string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery('se_item')
      ->accessCheck(TRUE)
      ->condition($field, '%' . $text . '%', 'LIKE')
      ->range(0, 10);

    $itemIds = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Item::loadMultiple($itemIds) as $entityId => $item) {
      $fields = array_filter([
        $description,
        $item->getName(),
        $item->se_serial->value ?? NULL,
        \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $item->se_sell_price->value),
      ]);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $key .= ' (!' . $entityId . ')';
      $matches[] = [
        'value' => $key,
        'label' => $key,
      ];
    }

    return $matches;
  }

  /**
   * Return information of $type with $text in $field, prefix with $description.
   *
   * @param string $description
   *   The text description for the type.
   * @param string $field
   *   The field to be searched.
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  #[ArrayShape([
    'value' => "string",
    'label' => "string",
  ])]
  private function findInformation(string $description, string $field, string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery('se_information')
      ->accessCheck(TRUE)
      ->condition($field, '%' . $text . '%', 'LIKE')
      ->range(0, 10);

    $itemIds = $query->execute();
    /** @var \Drupal\node\Entity\Node $information */
    foreach (Information::loadMultiple($itemIds) as $entityId => $information) {
      $key = $information->getName() . " (#$entityId)";
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $fields = array_filter([
        $description,
        $information->se_cu_ref->entity->name->value ?? NULL,
        $information->getName(),
      ]);
      $output = implode(' - ', $fields);
      $informationId = $information->id();
      $matches[] = [
        'value' => $key,
        'label' => $output . " (#$informationId)",
      ];
    }

    return $matches;
  }

}
