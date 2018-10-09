<?php

namespace Drupal\se_items\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\views\Views;

/**
* Plugin implementation of the 'selection' entity_reference.
*
* @EntityReferenceSelection(
*   id = "se_stock_item",
*   label = @Translation("Stock item selection"),
*   group = "se_stock_item",
*   weight = 1
* )
*/
class EntityItemSelection extends SelectionPluginBase implements ContainerFactoryPluginInterface {

  use SelectionTrait;

  /**
   * The loaded View object.
   *
   * @var \Drupal\views\ViewExecutable;
   */
  protected $view;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'view' => [
          'view_name' => NULL,
          'display_name' => NULL,
          'arguments' => [],
        ],
      ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $view_settings = $this->getConfiguration()['view'];
    $displays = Views::getApplicableViews('entity_reference_display');
    // Filter views that list the entity type we want, and group the separate
    // displays by view.
    $entity_type = $this->entityManager->getDefinition($this->configuration['target_type']);
    $view_storage = $this->entityManager->getStorage('view');

    $options = [];
    foreach ($displays as $data) {
      list($view_id, $display_id) = $data;
      $view = $view_storage->load($view_id);
      if (in_array($view->get('base_table'), [$entity_type->getBaseTable(), $entity_type->getDataTable()])) {
        $display = $view->get('display');
        $options[$view_id . ':' . $display_id] = $view_id . ' - ' . $display[$display_id]['display_title'];
      }
    }

    // The value of the 'view_and_display' select below will need to be split
    // into 'view_name' and 'view_display' in the final submitted values, so
    // we massage the data at validate time on the wrapping element (not
    // ideal).
    $form['view']['#element_validate'] = [[get_called_class(), 'settingsFormValidate']];

    if ($options) {
      $default = !empty($view_settings['view_name']) ? $view_settings['view_name'] . ':' . $view_settings['display_name'] : NULL;
      $form['view']['view_and_display'] = [
        '#type' => 'select',
        '#title' => $this->t('View used to select the entities'),
        '#required' => TRUE,
        '#options' => $options,
        '#default_value' => $default,
        '#description' => '<p>' . $this->t('Choose the view and display that select the entities that can be referenced.<br />Only views with a display of type "Entity Reference" are eligible.') . '</p>',
      ];

      $default = !empty($view_settings['arguments']) ? implode(', ', $view_settings['arguments']) : '';
      $form['view']['arguments'] = [
        '#type' => 'textfield',
        '#title' => $this->t('View arguments'),
        '#default_value' => $default,
        '#required' => FALSE,
        '#description' => $this->t('Provide a comma separated list of arguments to pass to the view.'),
      ];
    }
    else {
      if ($this->currentUser->hasPermission('administer views') && $this->moduleHandler->moduleExists('views_ui')) {
        $form['view']['no_view_help'] = [
          '#markup' => '<p>' . $this->t('No eligible views were found. <a href=":create">Create a view</a> with an <em>Entity Reference</em> display, or add such a display to an <a href=":existing">existing view</a>.', [
              ':create' => Url::fromRoute('views_ui.add')->toString(),
              ':existing' => Url::fromRoute('entity.view.collection')->toString(),
            ]) . '</p>',
        ];
      }
      else {
        $form['view']['no_view_help']['#markup'] = '<p>' . $this->t('No eligible views were found.') . '</p>';
      }
    }
    return $form;
  }

  /**
   * Initializes a view.
   *
   * @param string|null $match
   *   (Optional) Text to match the label against. Defaults to NULL.
   * @param string $match_operator
   *   (Optional) The operation the matching should be done with. Defaults
   *   to "CONTAINS".
   * @param int $limit
   *   Limit the query to a given number of items. Defaults to 0, which
   *   indicates no limiting.
   * @param array|null $ids
   *   Array of entity IDs. Defaults to NULL.
   *
   * @return bool
   *   Return TRUE if the view was initialized, FALSE otherwise.
   */
  protected function initializeView($match = NULL, $match_operator = 'CONTAINS', $limit = 0, $ids = NULL) {
    $view_name = $this->getConfiguration()['view']['view_name'];
    $display_name = $this->getConfiguration()['view']['display_name'];

    // Check that the view is valid and the display still exists.
    $this->view = Views::getView($view_name);
    if (!$this->view || !$this->view->access($display_name)) {
      \Drupal::messenger()->addWarning(t('The reference view %view_name cannot be found.', ['%view_name' => $view_name]));
      return FALSE;
    }
    $this->view->setDisplay($display_name);

    // Pass options to the display handler to make them available later.
    $entity_reference_options = [
      'match' => $match,
      'match_operator' => $match_operator,
      'limit' => $limit,
      'ids' => $ids,
    ];
    $this->view->displayHandlers->get($display_name)->setOption('entity_reference_options', $entity_reference_options);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getReferenceableEntities($match = NULL, $match_operator = 'CONTAINS', $limit = 0) {
    // By default, show all items
    $virtual = FALSE;

    $display_name = $this->getConfiguration()['view']['display_name'];
    $arguments = $this->getConfiguration()['view']['arguments'];
    $result = [];

    // @ at the start means show virtual items as well.
    if (stripos($match, '@') === 0) {
      $match = ltrim($match, '@');
      $virtual = TRUE;
    }
    if ($this->initializeView($match, $match_operator, $limit)) {
      // Adjust the filters before the results are retrieved.
      $filters = $this->view->display_handler->getOption('filters');

      // The default for the view is to_not show virtual,
      // so removing that will show all items.
      if ($virtual) {
        $filters['field_si_virtual_value']['value'] = 1;
      }
      $this->view->display_handler->overrideOption('filters', $filters);
      $result = $this->view->executeDisplay($display_name, $arguments);
    }

    $return = [];
    if ($result) {
      foreach ($this->view->result as $row) {
        $entity = $row->_entity;

        // This will be the item node.
        $relationship_entity = reset($row->_relationship_entities);
        $code = $relationship_entity->field_it_code->value;
        $price = $relationship_entity->field_it_price->value;

        // Construct the serial number, if this is not a virtual/service item.
        $serial = '';
        if (!$entity->field_si_virtual->value) {
          $serial = '#' . $entity->field_si_serial->value . '#';
        }

        // Format - Code #Serial# Desc - Price
        // TODO Currency format for price?
        $return[$entity->bundle()][$entity->id()] = $code . ' ' . $serial . ' ' . $entity->label() . ' - ' . $price;
      }
    }
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function countReferenceableEntities($match = NULL, $match_operator = 'CONTAINS') {
    $this->getReferenceableEntities($match, $match_operator);
    return $this->view->pager->getTotalItems();
  }

  /**
   * {@inheritdoc}
   */
  public function validateReferenceableEntities(array $ids) {
    // Most of this code is from Drupal\Core\Entity\Plugin\EntityReferenceSelection
    $result = [];
    $validate_stock = TRUE;

    if ($ids) {
      $node = \Drupal::routeMatch()->getParameter('node');
      if ($node->getType() == 'se_invoice') {
        $validate_stock = TRUE;
      }

      $configuration = $this->getConfiguration();
      $target_type = $configuration['target_type'];
      $entity_type = $this->entityManager->getDefinition($target_type);
      $query = $this->entityManager->getStorage($target_type)->getQuery();
      // Add entity-access tag.
      $query->addTag($target_type . '_access');

      // Add the Selection handler for system_query_entity_reference_alter().
      $query->addTag('entity_reference');
      $query->addMetaData('entity_reference_selection_handler', $this);

      $result = $query
        ->condition($entity_type->getKey('id'), $ids, 'IN')
        ->execute();

      $entities = $this->entityManager->getStorage($target_type)->loadMultiple($result);
      foreach ($entities as $item) {
        // Virtual items can be sold multiple times.
        if ($item->field_si_virtual->value) {
          continue;
        }

        // If the item is sold, its invalid.
        if ($validate_stock && !$item->field_si_sale_date->value !== 0) {
          // Unless its in the current node!
          if ($node->id() != $item->field_si_invoice_ref->target_id) {
            unset($result[$item->id()]);
          }
        }
      }
    }

    return $result;
  }

  /**
   * Element validate; Check View is valid.
   */
  public static function settingsFormValidate($element, FormStateInterface $form_state, $form) {
    // Split view name and display name from the 'view_and_display' value.
    if (!empty($element['view_and_display']['#value'])) {
      list($view, $display) = explode(':', $element['view_and_display']['#value']);
    }
    else {
      $form_state->setError($element, t('The views entity selection mode requires a view.'));
      return;
    }

    // Explode the 'arguments' string into an actual array. Beware, explode()
    // turns an empty string into an array with one empty string. We'll need an
    // empty array instead.
    $arguments_string = trim($element['arguments']['#value']);
    if ($arguments_string === '') {
      $arguments = [];
    }
    else {
      // array_map() is called to trim whitespaces from the arguments.
      $arguments = array_map('trim', explode(',', $arguments_string));
    }

    $value = ['view_name' => $view, 'display_name' => $display, 'arguments' => $arguments];
    $form_state->setValueForElement($element, $value);
  }

}