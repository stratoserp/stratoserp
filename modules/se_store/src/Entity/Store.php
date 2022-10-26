<?php

declare(strict_types=1);

namespace Drupal\se_store\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Store entity.
 *
 * @ingroup se_store
 *
 * @ContentEntityType(
 *   id = "se_store",
 *   label = @Translation("Store"),
 *   label_collection = @Translation("Stores"),
 *   handlers = {
 *     "storage" = "Drupal\se_store\StoreStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_store\StoreListBuilder",
 *     "views_data" = "Drupal\se_store\Entity\StoreViewsData",
 *     "translation" = "Drupal\se_store\StoreTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_store\Form\StoreForm",
 *       "add" = "Drupal\se_store\Form\StoreForm",
 *       "edit" = "Drupal\se_store\Form\StoreForm",
 *       "delete" = "Drupal\se_store\Form\StoreDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_store\StoreHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_store\StoreAccessControlHandler",
 *   },
 *   base_table = "se_store",
 *   data_table = "se_store_field_data",
 *   revision_table = "se_store_revision",
 *   revision_data_table = "se_store_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer store entities",
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
 *     "canonical" = "/store/{se_store}",
 *     "add-form" = "/store/add",
 *     "edit-form" = "/store/{se_store}/edit",
 *     "delete-form" = "/store/{se_store}/delete",
 *     "version-history" = "/store/{se_store}/revisions",
 *     "revision" = "/store/{se_store}/revisions/{se_store_revision}/view",
 *     "revision_revert" = "/store/{se_store}/revisions/{se_store_revision}/revert",
 *     "revision_delete" = "/store/{se_store}/revisions/{se_store_revision}/delete",
 *     "translation_revert" = "/store/{se_store}/revisions/{se_store_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/store-list",
 *   },
 *   field_ui_base_route = "se_store.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Store extends StratosEntityBase implements StoreInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'co';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name']->setDescription(t('The name of the store.'));

    return $fields;
  }

}
