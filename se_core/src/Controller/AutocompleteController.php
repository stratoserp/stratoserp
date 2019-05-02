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
  public function handleAutocomplete(Request $request) {
    $matches = [];

    // Get the typed string from the URL, if it exists.
    if ($input = $request->query->get('q')) {
      $search_string = Tags::explode($input);
      $search_string = mb_strtolower(array_pop($search_string));

      $db = \Drupal::database();
      // TODO - Range limit
      $query = \Drupal::entityQuery('node')
        ->condition('status', 1)
        ->condition('title', '%' . Database::getConnection()->escapeLike($search_string) . '%', 'LIKE')
        ->condition('type', 'se_customer');

      $node_ids = $query->execute();
      $result = Node::loadMultiple($node_ids);

      /** @var \Drupal\node\Entity\Node $node */
      foreach ($result as $entity_id => $node) {
        $key = $node->getTitle() . " ($entity_id)";
        $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
        // Names containing commas or quotes must be wrapped in quotes.
        $key = Tags::encode($key);
        $matches[] = ['value' => $key, 'label' => $node->getTitle()];
      }
    }

    return new JsonResponse($matches);
  }
}