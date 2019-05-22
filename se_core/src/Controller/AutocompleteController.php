<?php

namespace Drupal\se_core\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Database;
use Drupal\node\Entity\Node;
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

      $customers = $this->findItems('se_customer', 'Customer', 'title', $search_string);
      $invoices = $this->findItems('se_invoice', 'Invoice', 'field_in_id', $search_string);
      $quotes = $this->findItems('se_quote', 'Quote', 'field_qu_id', $search_string);

      $matches = array_merge($customers, $invoices, $quotes);

    }

    return new JsonResponse($matches);
  }

  private function findItems($type, $description, $field, $text): array {
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

}