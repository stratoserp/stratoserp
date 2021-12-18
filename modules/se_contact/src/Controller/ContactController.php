<?php

declare(strict_types=1);

namespace Drupal\se_contact\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\se_contact\Entity\Contact;
use Drupal\se_contact\Entity\ContactInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ContactController.
 *
 *  Returns responses for Contact routes.
 */
class ContactController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Contact revision.
   *
   * @param int $se_contact_revision
   *   The Contact revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_contact_revision) {
    $se_contact = $this->entityTypeManager()->getStorage('se_contact')
      ->loadRevision($se_contact_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_contact');

    return $view_builder->view($se_contact);
  }

  /**
   * Page title callback for a Contact revision.
   *
   * @param int $se_contact_revision
   *   The Contact revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_contact_revision) {
    $se_contact = $this->entityTypeManager()->getStorage('se_contact')
      ->loadRevision($se_contact_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_contact->label(),
      '%date' => $this->dateFormatter->format($se_contact->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Contact.
   *
   * @param \Drupal\se_contact\Entity\ContactInterface $se_contact
   *   A Contact object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(ContactInterface $se_contact) {
    $account = $this->currentUser();
    $se_contact_storage = $this->entityTypeManager()->getStorage('se_contact');

    $langcode = $se_contact->language()->getId();
    $langname = $se_contact->language()->getName();
    $languages = $se_contact->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_contact->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_contact->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all contact revisions") || $account->hasPermission('administer contact entities')));
    $delete_permission = (($account->hasPermission("delete all contact revisions") || $account->hasPermission('administer contact entities')));

    $rows = [];

    $vids = $se_contact_storage->revisionIds($se_contact);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_contact\ContactInterface $revision */
      $revision = $se_contact_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_contact->getRevisionId()) {
          $link = $this->l($date, new Url('entity.se_contact.revision', [
            'se_contact' => $se_contact->id(),
            'se_contact_revision' => $vid,
          ]));
        }
        else {
          $link = $se_contact->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.se_contact.translation_revert', [
                'se_contact' => $se_contact->id(),
                'se_contact_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_contact.revision_revert', [
                'se_contact' => $se_contact->id(),
                'se_contact_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_contact.revision_delete', [
                'se_contact' => $se_contact->id(),
                'se_contact_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['se_contact_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

  /**
   * Provides the entity submission form for payment creation from an invoice.
   *
   * @param \Drupal\Core\Entity\EntityInterface $source
   *   Source entity to copy data from.
   *
   * @return array
   *   An entity submission form.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function fromBusiness(EntityInterface $source): array {
    $entity = Contact::create([
      'bundle' => 'se_contact',
    ]);

    $business = \Drupal::service('se_business.service')->lookupBusiness($source);
    $entity->se_bu_ref = $business;

    return $this->entityFormBuilder()->getForm($entity);
  }


}
