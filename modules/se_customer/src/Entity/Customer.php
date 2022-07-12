<?php

declare(strict_types=1);

namespace Drupal\se_customer\Entity;

use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Customer entity.
 *
 * Note that this could be converted to use bundles and separate the customer
 * and suppliers to different types, but in the end, there would still only
 * be one field different, so leaving that for now.
 *
 * @ingroup se_customer
 *
 * @ContentEntityType(
 *   id = "se_customer",
 *   label = @Translation("Customer"),
 *   label_collection = @Translation("Customers"),
 *   handlers = {
 *     "storage" = "Drupal\se_customer\CustomerStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_customer\CustomerListBuilder",
 *     "views_data" = "Drupal\se_customer\Entity\CustomerViewsData",
 *     "translation" = "Drupal\se_customer\CustomerTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_customer\Form\CustomerForm",
 *       "add" = "Drupal\se_customer\Form\CustomerForm",
 *       "edit" = "Drupal\se_customer\Form\CustomerForm",
 *       "delete" = "Drupal\se_customer\Form\CustomerDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_customer\CustomerHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_customer\CustomerAccessControlHandler",
 *   },
 *   base_table = "se_customer",
 *   data_table = "se_customer_field_data",
 *   revision_table = "se_customer_revision",
 *   revision_data_table = "se_customer_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer customer entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/customer/{se_customer}",
 *     "add-form" = "/customer/add",
 *     "edit-form" = "/customer/{se_customer}/edit",
 *     "delete-form" = "/customer/{se_customer}/delete",
 *     "version-history" = "/customer/{se_customer}/revisions",
 *     "revision" = "/customer/{se_customer}/revisions/{se_customer_revision}/view",
 *     "revision_revert" = "/customer/{se_customer}/revisions/{se_customer_revision}/revert",
 *     "revision_delete" = "/customer/{se_customer}/revisions/{se_customer_revision}/delete",
 *     "translation_revert" = "/customer/{se_customer}/revisions/{se_customer_revision}/revert/{langcode}",
 *     "collection" = "/se/customers",
 *   },
 *   field_ui_base_route = "se_customer.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Customer extends StratosEntityBase implements CustomerInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'bu';
  }

  /**
   * {@inheritdoc}
   */
  public function getBalance(): int {
    return (int) $this->se_balance->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setBalance(int $value): int {
    $this->set('se_balance', $value);
    $this->save();
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public function adjustBalance(int $value): int {
    if ($value === 0) {
      return $this->getBalance();
    }

    $this->se_balance->value = (int) $this->se_balance->value + $value;
    $this->save();
    return $this->getBalance();
  }

  /**
   * {@inheritdoc}
   */
  public function setSkipXeroEvents(): void {
    $this->skipCustomerXeroEvents = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isSkipXeroEvents(): bool {
    return $this->skipCustomerXeroEvents ?? FALSE;
  }

}
