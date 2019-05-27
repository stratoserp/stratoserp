<?php

namespace Drupal\se_core\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
use Drupal\se_information\Entity\Information;
use Drupal\se_item\Entity\Item;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AutocompleteController extends ControllerBase {

  /**
   * Handler for autocomplete request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @param $field_name
   * @param $count
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function handleAutocomplete(Request $request): JsonResponse {
    $matches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $search_string = Tags::explode($input);
      $search_string = mb_strtolower(array_pop($search_string));

      $customers = $this->findNodes('se_customer', 'Customer', 'title', $search_string);
      $invoices = $this->findNodes('se_invoice', 'Invoice', 'field_in_id', $search_string);
      $quotes = $this->findNodes('se_quote', 'Quote', 'field_qu_id', $search_string);
      $items = $this->findItems('se_item', 'Item', 'name', $search_string);
      $serials = $this->findItems('se_item', 'Item', 'field_it_serial', $search_string);
      $information = $this->findInformation('se_document', 'Document', 'name', $search_string);

      $matches = array_merge($customers, $invoices, $quotes, $items, $serials, $information);

    }

    return new JsonResponse($matches);
  }

  private function findNodes($type, $description, $field, $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('node')
      ->condition('status', 1)
      ->condition($field, '%' . Database::getConnection()
          ->escapeLike($text) . '%', 'LIKE')
      ->condition('type', $type)
      ->range(0, 10);

    $node_ids = $query->execute();
    $result = Node::loadMultiple($node_ids);

    /** @var \Drupal\node\Entity\Node $node */
    foreach ($result as $entity_id => $node) {
      $key = $node->getTitle() . " ($entity_id)";
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      if ($type === 'se_customer') {
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
      $matches[] = ['value' => $key, 'label' => $output_description];
    }

    return $matches;
  }

  private function findItems($type, $description, $field, $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_item')
      //->condition('status', 1)
      ->condition($field, '%' . Database::getConnection()
          ->escapeLike($text) . '%', 'LIKE')
      //->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    $result = Item::loadMultiple($item_ids);

    /** @var \Drupal\node\Entity\Node $item */
    foreach ($result as $entity_id => $item) {
      $fields = [
        $description,
        $item->getName(),
        $item->field_it_serial->value ?: NULL,
        trim(sprintf("%9.2f", $item->field_it_sell_price->value)) // @todo currency service
      ];
      $fields = array_filter($fields);
      $key = implode(' - ', $fields);
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $key .= ' (!' . $entity_id . ')';
      $id = $item->id();
      $matches[] = ['value' => $key, 'label' => $key, "(!$id)"];
    }

    return $matches;
  }

  private function findInformation($type, $description, $field, $text): array {
    $matches = [];

    $query = \Drupal::entityQuery('se_information')
      //->condition('status', 1)
      ->condition($field, '%' . Database::getConnection()
          ->escapeLike($text) . '%', 'LIKE')
      //->condition('type', $type)
      ->range(0, 10);

    $item_ids = $query->execute();
    $result = Information::loadMultiple($item_ids);

    /** @var \Drupal\node\Entity\Node $information */
    foreach ($result as $entity_id => $information) {
      $key = $information->getName() . " (#$entity_id)";
      $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
      // Names containing commas or quotes must be wrapped in quotes.
      $key = Tags::encode($key);
      $output_description = implode(' - ', [
        $description,
        $information->field_bu_ref->entity->title->value,
        $information->getName(),
      ]);
      $id = $information->id();
      $matches[] = ['value' => $key, 'label' => $output_description , "(#$id)"];
    }

    return $matches;
  }

}