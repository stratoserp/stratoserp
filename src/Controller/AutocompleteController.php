<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
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

      $businesss = $this->findBusinesses($searchString);
      $contacts = $this->findNodes('se_contact', 'Contact', 'title', $searchString);
      $invoices = $this->findNodes('se_invoice', 'Invoice', 'se_in_id', $searchString);
      $quotes = $this->findNodes('se_quote', 'Quote', 'se_qu_id', $searchString);
      $items = $this->findItems('se_item', 'Item', 'name', $searchString);
      $serials = $this->findItems('se_item', 'Item', 'se_it_serial', $searchString);
      $information = $this->findInformation('se_document', 'Document', 'name', $searchString);

      $matches = array_merge($businesss, $contacts, $invoices, $quotes, $items, $serials, $information);

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
  private function findNodes($type, $description, $field, $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition($field, '%' .
                  Database::getConnection()->escapeLike($text) . '%', 'LIKE')
      ->condition('type', $type)
      ->range(0, 10);
    $node_ids = $query->execute();

    /** @var \Drupal\node\Entity\Node $node */
    foreach (Node::loadMultiple($node_ids) as $entity_id => $node) {
      $key = $node->getTitle() . " ($entity_id)";
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      if ($type === 'se_business') {
        $output_description = implode(' - ', [
          $description,
          $node->getTitle(),
        ]);
      }
      else {
        $output_description = implode(' - ', [
          $description,
          $node->getTitle(),
          '#' . $node->{$field}->value,
        ]);
      }
      $matches[] = [
        'value' => $key,
        'label' => $output_description,
      ];
    }

    return $matches;
  }

  /**
   * Return businesss matching text.
   *
   * @param string $text
   *   The text to search for.
   *
   * @return array
   *   An array of matches.
   */
  private function findBusinesses($text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_business')
      ->condition('name', '%' .
        Database::getConnection()->escapeLike($text) . '%', 'LIKE')
      // ->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Business::loadMultiple($item_ids) as $entity_id => $business) {
      $fields = [
        $business->getName(),
      ];
      $fields = array_filter($fields);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $output_description = implode(' - ', [
        $business->getName(),
      ]);

      $key .= ' (' . $entity_id . ')';
      $businessId = $business->id();
      $matches[] = [
        'value' => $key,
        'label' => $output_description . "($businessId)",
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
  private function findItems($type, $description, $field, $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_item')
      ->condition($field, '%' .
                  Database::getConnection()->escapeLike($text) . '%', 'LIKE')
      // ->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    /** @var \Drupal\node\Entity\Node $item */
    foreach (Item::loadMultiple($item_ids) as $entity_id => $item) {
      $fields = [
        $description,
        $item->getName(),
        $item->se_it_serial->value ?: NULL,
        \Drupal::service('se_accounting.currency_format')->formatDisplay((int) $item->se_it_sell_price->value),
      ];
      $fields = array_filter($fields);
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
  private function findInformation($type, $description, $field, $text): array {
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
      $output_description = implode(' - ', [
        $description,
        $information->se_bu_ref->entity->title->value,
        $information->getName(),
      ]);

      $informationId = $information->id();
      $matches[] = [
        'value' => $key,
        'label' => $output_description . " (#$informationId)",
      ];
    }

    return $matches;
  }

}
