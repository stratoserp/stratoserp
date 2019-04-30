<?php

namespace Drupal\se_report\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\se_report\ReportUtilityTrait;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsPreconfigurationInterface;

/**
 * Class ItemBreakdownAction
 *
 * @Action(
 *   id = "item_breakdown_report_action",
 *   label = @Translation("Item breakdown report"),
 *   type = "node",
 *   confirm = FALSE
 * )
 *
 * TODO: Dependency injection.
 *
 */
class ItemBreakdownReportAction extends ViewsBulkOperationsActionBase implements ViewsBulkOperationsPreconfigurationInterface {

  use ReportUtilityTrait;

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityTypeId() === 'node') {
      $access = $object->access('view', $account, TRUE);
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    // Setup things for some persistence.
    $this->setBatchId();
    $this->createLoadBatchInfo();
    $this->createLoadReportNode();
    $report_array = $this->getBatchDataByKey('report_array') ?: [];

    $data = $this->convertParagraphsToArray($entity);

    foreach ($data as $code => $amount) {
      if (isset($report_array[$code])) {
        $report_array[$code] += $amount;
      }
      else {
        $report_array[$code] = $amount;
      }
    }

    // Store the batch data between action iterations
    $this->setBatchData('report_array', $report_array);
    $progress = $this->getBatchDataByKey('progress') + 1;
    $this->setBatchData('progress', $progress);
    $total = $this->getBatchDataByKey('total');

    // Check if we're finished, and if so, update report node, set redirect
    if ($progress >= $total) {
      $this->report_node->field_re_parameters->value = json_encode($this->getBatchDataByKey('input_parameters'));
      $this->report_node->field_re_json_data->value = json_encode($report_array);
      $this->report_node->field_re_raw_data->value = $this->createCSV($report_array);
      $this->report_node->save();

      // Set new redirect url (hmm doesn't work).
      //$this->context['results']['redirect_url'] = Url::fromUri('internal:/node/' . $this->report_node->id());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPreConfigurationForm(array $form, array $values, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    // Retrieve the views parameters so we can auto update this
    $form_storage = $form_state->getStorage();

    $input_parameters = $form_storage['views_bulk_operations'];
    foreach (['list', 'redirect_url', 'entity_labels', 'selected_count', 'sandbox', 'results'] as $field) {
      unset($input_parameters[$field]);
    }

    $form['input_parameters'] = [
      '#type' => 'value',
      '#value' => $input_parameters,
    ];

    // TODO Convert this to a form alter of the views exposed form.
    $form['business_ref'] = [
      '#title' => 'Business reference',
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#selection_settings' => ['target_bundles' => ['node' => 'se_customer']],
      '#description' => 'Associate/relate the report with a business.'
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['input_parameters'] = $form_state->getValue('input_parameters');
    $this->configuration['business_ref'] = $form_state->getValue('business_ref');
  }


  /**
   * {@inheritdoc}
   */
  public function setContext(array &$context) {
    $this->context['sandbox'] = &$context['sandbox'];
    $this->context['results'] = &$context['results'];
    foreach ($context as $key => $item) {
      if ($key === 'sandbox' || $key === 'results') {
        continue;
      }
      $this->context[$key] = $item;
    }
  }

}
