<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\se_business\Entity\Business;
use Drupal\se_information\Entity\Information;
use Drupal\se_item\Entity\Item;
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
    $matches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $searchString = Tags::explode($input);
      $searchString = mb_strtolower(array_pop($searchString));

      $business = $this->findBusinesses($searchString);
      $contacts = $this->findEntity('se_contact', 'Contact', 'name', $searchString);
      $invoices = $this->findEntity('se_invoice', 'Invoice', 'id', $searchString);
      $quotes = $this->findEntity('se_quote', 'Quote', 'id', $searchString);
      $items = $this->findItems('se_item', 'Item', 'name', $searchString);
      $serials = $this->findItems('se_item', 'Item', 'se_it_serial', $searchString);
      $information = $this->findInformation('se_document', 'Document', 'name', $searchString);

      $matches = array_merge($business, $contacts, $invoices, $quotes, $items, $serials, $information);

    }

    return new JsonResponse($matches);
  }

  /**
   * Return nodes of $type with $text in $field, prefix with $description.
   *
   * @param string $type
   *   The type of node to search for.
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
  private function findEntity(string $type, string $description, string $field, string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery($type)
      ->condition($field, '%' . $text . '%', 'LIKE')
      ->range(0, 10);
    $entity_ids = $query->execute();

    $entities = \Drupal::entityTypeManager()->getStorage($type)->loadMultiple($entity_ids);
    foreach ($entities as $entity_id => $entity) {
      $key = sprintf("%s (%s-%s)", $entity->getName(), $entity->getSearchPrefix(), $entity_id);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $fields = array_filter([
        $description,
        $key,
      ]);
      $output_description = implode(' - ', $fields);
      $matches[] = [
        'value' => $key,
        'label' => $output_description,
      ];
    }

    return $matches;
  }

  /**
   * Return business matching text.
   *
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  private function findBusinesses(string $text): array {
    $matches = [];

    $text = Database::getConnection()->escapeLike($text);
    $query = \Drupal::entityQuery('se_business')
      ->condition('name', '%' . $text . '%', 'LIKE')
      ->range(0, 10);

    $item_ids = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Business::loadMultiple($item_ids) as $entity_id => $business) {
      $fields = array_filter([
        $business->getName(),
      ]);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $output_description = $business->getName();

      $key .= ' (' . $entity_id . ')';
      $businessId = $business->id();
      $matches[] = [
        'value' => $key,
        'label' => $output_description . " - ($businessId)",
      ];
    }

    return $matches;
  }

  /**
   * Return items of $type with $text in $field, prefix with $description.
   *
   * @param string $type
   *   The type of item to search for.
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
  private function findItems(string $type, string $description, string $field, string $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_item')
      ->condition($field, '%' .
                  Database::getConnection()->escapeLike($text) . '%', 'LIKE')
      // ->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Item::loadMultiple($item_ids) as $entity_id => $item) {
      $fields = array_filter([
        $description,
        $item->getName(),
        $item->se_it_serial->value ?: NULL,
        \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $item->se_it_sell_price->value),
      ]);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $key .= ' (!' . $entity_id . ')';
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
   * @param string $type
   *   The type of information to search for.
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
  private function findInformation(string $type, string $description, string $field, string $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_information')
      ->condition($field, '%' .
                  Database::getConnection()->escapeLike($text) . '%', 'LIKE')
      // ->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    /** @var \Drupal\node\Entity\Node $information */
    foreach (Information::loadMultiple($item_ids) as $entity_id => $information) {
      $key = $information->getName() . " (#$entity_id)";
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $fields = array_filter([
        $description,
        $information->se_bu_ref->entity->name->value ?? NULL,
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
