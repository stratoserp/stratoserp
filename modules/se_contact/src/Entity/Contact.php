<?php

declare(strict_types=1);

namespace Drupal\se_contact\Entity;

use Drupal\stratoserp\Entity\StratosEntityBase;

/**
 * Defines the Contact entity.
 *
 * @ingroup se_contact
 *
 * @ContentEntityType(
 *   id = "se_contact",
 *   label = @Translation("Contact"),
 *   label_collection = @Translation("Contacts"),
 *   handlers = {
 *     "storage" = "Drupal\se_contact\ContactStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\se_contact\ContactListBuilder",
 *     "views_data" = "Drupal\se_contact\Entity\ContactViewsData",
 *     "translation" = "Drupal\se_contact\ContactTranslationHandler",
 *     "form" = {
 *       "default" = "Drupal\se_contact\Form\ContactForm",
 *       "add" = "Drupal\se_contact\Form\ContactForm",
 *       "edit" = "Drupal\se_contact\Form\ContactForm",
 *       "delete" = "Drupal\se_contact\Form\ContactDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\se_contact\ContactHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\se_contact\ContactAccessControlHandler",
 *   },
 *   base_table = "se_contact",
 *   data_table = "se_contact_field_data",
 *   revision_table = "se_contact_revision",
 *   revision_data_table = "se_contact_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer contact entities",
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
 *     "canonical" = "/contact/{se_contact}",
 *     "add-form" = "/contact/add",
 *     "edit-form" = "/contact/{se_contact}/edit",
 *     "delete-form" = "/contact/{se_contact}/delete",
 *     "version-history" = "/contact/{se_contact}/revisions",
 *     "revision" = "/contact/{se_contact}/revisions/{se_contact_revision}/view",
 *     "revision_revert" = "/contact/{se_contact}/revisions/{se_contact_revision}/revert",
 *     "revision_delete" = "/contact/{se_contact}/revisions/{se_contact_revision}/delete",
 *     "translation_revert" = "/contact/{se_contact}/revisions/{se_contact_revision}/revert/{langcode}",
 *     "collection" = "/se/customers/contact-list",
 *   },
 *   field_ui_base_route = "se_contact.settings",
 *   revision_metadata_keys = {
 *     "revision_user" = "revision_user",
 *     "revision_created" = "revision_created",
 *     "revision_log_message" = "revision_log_message"
 *   }
 * )
 */
class Contact extends StratosEntityBase implements ContactInterface {

  /**
   * {@inheritdoc}
   */
  public function getSearchPrefix(): string {
    return 'co';
  }

}
