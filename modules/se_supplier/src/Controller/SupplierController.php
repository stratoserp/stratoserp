<?php

declare(strict_types=1);

namespace Drupal\se_supplier\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\se_supplier\Entity\SupplierInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SupplierController.
 *
 *  Returns responses for Supplier routes.
 */
class SupplierController extends ControllerBase {

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
   * Displays a Supplier revision.
   *
   * @param int $se_supplier_revision
   *   The Supplier revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($se_supplier_revision) {
    $se_supplier = $this->entityTypeManager()->getStorage('se_supplier')
      ->loadRevision($se_supplier_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('se_supplier');

    return $view_builder->view($se_supplier);
  }

  /**
   * Page title callback for a Supplier revision.
   *
   * @param int $se_supplier_revision
   *   The Supplier revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($se_supplier_revision) {
    $se_supplier = $this->entityTypeManager()->getStorage('se_supplier')
      ->loadRevision($se_supplier_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $se_supplier->label(),
      '%date' => $this->dateFormatter->format($se_supplier->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Supplier.
   *
   * @param \Drupal\se_supplier\Entity\SupplierInterface $se_supplier
   *   A Supplier object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(SupplierInterface $se_supplier) {
    $account = $this->currentUser();
    $se_supplier_storage = $this->entityTypeManager()->getStorage('se_supplier');

    $langcode = $se_supplier->language()->getId();
    $langname = $se_supplier->language()->getName();
    $languages = $se_supplier->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', [
      '@langname' => $langname,
      '%title' => $se_supplier->label(),
    ]) : $this->t('Revisions for %title', ['%title' => $se_supplier->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all supplier revisions") || $account->hasPermission('administer supplier entities')));
    $delete_permission = (($account->hasPermission("delete all supplier revisions") || $account->hasPermission('administer supplier entities')));

    $rows = [];

    $vids = $se_supplier_storage->revisionIds($se_supplier);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\se_supplier\Entity\SupplierInterface $revision */
      $revision = $se_supplier_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link for revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $se_supplier->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.se_supplier.revision', [
            'se_supplier' => $se_supplier->id(),
            'se_supplier_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $se_supplier->toLink($date)->toString();
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
              Url::fromRoute('entity.se_supplier.translation_revert', [
                'se_supplier' => $se_supplier->id(),
                'se_supplier_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.se_supplier.revision_revert', [
                'se_supplier' => $se_supplier->id(),
                'se_supplier_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.se_supplier.revision_delete', [
                'se_supplier' => $se_supplier->id(),
                'se_supplier_revision' => $vid,
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

    $build['se_supplier_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
