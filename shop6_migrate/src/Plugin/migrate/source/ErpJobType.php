<?php

namespace Drupal\shop6_migrate\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Migration of job type taxonomy from drupal6 erp system.
 *
 * @MigrateSource(
 *   id = "upgrade_d6_term_erp_job_type",
 *   source_module = "erp_job_type_made_up"
 * )
 */
class ErpJobType extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('term_data', 'td');

    $query->fields('td');
    $query->where('vid = 3');
    $query->orderBy('tid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'tid'          => $this->t('Term id'),
      'vid'          => $this->t('Vocabulary id'),
      'name'         => $this->t('Term name'),
      'description'  => $this->t('Term description'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'td',
      ],
    ];
  }

}
