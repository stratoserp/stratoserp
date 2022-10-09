<?php

declare(strict_types=1);

namespace Drupal\stratoserp\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\se_customer\Entity\Customer;
use Drupal\se_information\Entity\Information;
use Drupal\se_item\Entity\Item;
use Drupal\stratoserp\Constants;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide the main search form.
 */
class SearchForm extends FormBase {

  /**
   * The file storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $messenger;

  /**
   * Constructs a form object for image dialog.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The file storage service.
   */
  public function __construct(MessengerInterface $messenger) {
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'stratoserp_search_form';
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
      '#size' => 30,
      '#autocomplete_route_name' => 'stratoserp.search',
      '#autocomplete_route_parameters' => [
        'field_name' => 'search',
        'count' => 20,
      ],
    ];

    $build['submit'] = [
      '#title' => t('Load'),
      '#type' => 'submit',
      '#value' => t('Load'),
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
      // No submission = no messenger.
      return $this->messenger->addError(t('No search string found'));
    }

    if ($this->searchLoadCustomer($values['search'], $form_state)) {
      return NULL;
    }

    if ($this->searchLoadEntity($values['search'], $form_state)) {
      return NULL;
    }

    if ($this->searchLoadItem($values['search'], $form_state)) {
      return NULL;
    }

    if ($this->searchLoadInformation($values['search'], $form_state)) {
      return NULL;
    }

    // Otherwise, perform a full text search, something like
    // https://www.drupal.org/docs/8/modules/search-api/developer-documentation/executing-a-search-in-code
  }

  /**
   * Extract entity information from a search string and try to load it.
   *
   * @param string $search
   *   The string the user entered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Messenger\MessengerInterface|false
   *   A message or false if it wasn't this type.
   */
  protected function searchLoadCustomer($search, FormStateInterface $form_state) {
    // If the user has chosen a node from the popup, load it.
    if (preg_match("/.+\s\(([^!#)[a-zA-Z]+)\)/", $search, $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        return $this->messenger->addMessage(t('No matches found'));
      }

      if (!Customer::load($match)) {
        return $this->messenger->addError(t('Invalid node'));
      }

      $form_state->setRedirect('entity.se_customer.canonical', ['se_customer' => $match]);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Extract entity information from a search string and try to load it.
   *
   * @param string $search
   *   The string the user entered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Messenger\MessengerInterface|false
   *   A message or false if it wasn't this type.
   */
  protected function searchLoadEntity($search, FormStateInterface $form_state) {
    // If the user has chosen a node from the popup, load it.
    if (preg_match("/\((..)-(\d+)\)/", $search, $regexMatches)) {
      [, $type, $code] = $regexMatches;
      if (empty($type) || empty($code)) {
        return $this->messenger->addMessage(t('No matches found'));
      }

      $fullType = Constants::SE_ENTITY_LOOKUP[$type]['type'];
      $entity = \Drupal::entityTypeManager()->getStorage($fullType)->load($code);
      if (!$entity) {
        return $this->messenger->addError(t('Invalid entity'));
      }

      $form_state->setRedirect('entity.' . $fullType . '.canonical', [$fullType => $code]);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Extract item information from a search string and try to load it.
   *
   * @param string $search
   *   The string the user entered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Messenger\MessengerInterface|false
   *   A message or false if it wasn't this type.
   */
  protected function searchLoadItem($search, FormStateInterface $form_state) {
    // Maybe the user chose an item.
    if (preg_match("/.+\s\(!([^)]+)\)/", $search, $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        return $this->messenger->addMessage(t('No matches found'));
      }

      if (!Item::load($match)) {
        return $this->messenger->addError(t('Invalid item'));
      }

      $form_state->setRedirect('entity.se_item.canonical', ['se_item' => $match]);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Extract information from a search string and try to load it.
   *
   * @param string $search
   *   The string the user entered.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\Core\Messenger\MessengerInterface|false
   *   A message or false if it wasn't this type.
   */
  protected function searchLoadInformation($search, FormStateInterface $form_state) {
    // Maybe the user chose information.
    if (preg_match("/.+\s\(#([^)]+)\)/", $search, $matches)) {
      $match = $matches[1];
      if (empty($match)) {
        return $this->messenger->addMessage(t('No matches found'));
      }

      if (!Information::load($match)) {
        return $this->messenger->addError(t('Invalid information'));
      }

      $form_state->setRedirect('entity.se_information.canonical', ['se_information' => $match]);
      return TRUE;
    }

    return FALSE;
  }

}
