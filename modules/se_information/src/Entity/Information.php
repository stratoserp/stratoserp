<?php

declare(strict_types=1);

namespace Drupal\se_information\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Information entity.
 *
 * @ingroup se_information
 *
 * @ContentEntityType(
 *   id = "se_information",
 *   label = @Translation("Information"),
 *   label_collection = @Translation("Information"),
 *   bundle_label = @Translation("Information type"),
 *   handlers = {
 *     "storage" = "Drupal\se_information\InformationStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_information\InformationListBuilder",
 *     "views_data" = "Drupal\se_information\Entity\InformationViewsData",
 *     "translation" = "Drupal\se_information\InformationTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_information\Form\InformationForm",
 *       "add" = "Drupal\se_information\Form\InformationForm",
 *       "edit" = "Drupal\se_information\Form\InformationForm",
 *       "delete" = "Drupal\se_information\Form\InformationDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_information\InformationHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_information\InformationAccessControlHandler",
 *   },
 *   base_table = "se_information",
 *   data_table = "se_information_field_data",
 *   revision_table = "se_information_revision",
 *   revision_data_table = "se_information_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer information entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "published" = "status",
 *     "uid" = "uid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/information/{se_information}",
 *     "add-page" = "/information/add",
 *     "add-form" = "/information/add/{se_information_type}",
 *     "edit-form" = "/information/{se_information}/edit",
 *     "delete-form" = "/information/{se_information}/delete",
 *     "version-history" = "/information/{se_information}/revisions",
 *     "revision" = "/information/{se_information}/revisions/{se_information_revision}/view",
 *     "revision_revert" = "/information/{se_information}/revisions/{se_information_revision}/revert",
 *     "revision_delete" = "/information/{se_information}/revisions/{se_information_revision}/delete",
 *     "translation_revert" = "/information/{se_information}/revisions/{se_information_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/information-list",
 *   },
 *   bundle_entity_type = "se_information_type",
 *   field_ui_base_route = "entity.se_information_type.edit_form",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Information extends StratosEntityBase implements InformationInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'if';
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name']->setDescription(t('The name of the information.'));

    return $fields;
  }

}
