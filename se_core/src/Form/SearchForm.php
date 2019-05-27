<?php

namespace Drupal\se_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\Messenger;

class SearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'se_core_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getPageTitle() {
    return t('Search');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $build['search'] = [
      '#title' => t('Enter customer name, id of invoice or quote or simply search.'),
      '#type' => 'textfield',
      '#autocomplete_route_name' => 'se_core.search',
      '#autocomplete_route_parameters' => ['field_name' => 'search', 'count' => 20],
    ];

    $build['submit'] = [
      '#title' => t('Search'),
      '#type' => 'submit',
      '#value' => t('Search'),
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->cleanValues();
    $values = $form_state->getValues();

    if (empty($values['search'])) {
      $this->messenger->addMessage(t('No search string found'));
      return;
    }

    // If the user has chosen a node from the popup, load it.
    if (preg_match("/.+\s\(([^!#)\w]+)\)/", $values['search'], $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        $this->messenger->addMessage(t('No matches found'));
        return;
      }

      $form_state->setRedirect('entity.node.canonical', ['node' => $match]);
      return;
    }

    // Maybe the user chose an item.
    if (preg_match("/.+\s\(!([^)]+)\)/", $values['search'], $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        $this->messenger->addMessage(t('No matches found'));
        return;
      }

      $form_state->setRedirect('entity.se_item.canonical', ['se_item' => $match]);
      return;
    }

    // Maybe the user chose information.
    if (preg_match("/.+\s\(#([^)]+)\)/", $values['search'], $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        $this->messenger->addMessage(t('No matches found'));
        return;
      }

      $form_state->setRedirect('entity.se_information.canonical', ['se_information' => $match]);
      return;
    }



    // Otherwise, perform a full text search, something like
    // https://www.drupal.org/docs/8/modules/search-api/developer-documentation/executing-a-search-in-code

  }

}
