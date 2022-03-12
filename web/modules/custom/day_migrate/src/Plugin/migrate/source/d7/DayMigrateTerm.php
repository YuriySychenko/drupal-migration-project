<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\d7\Term;

/**
 * Taxonomy term source from database.
 *
 * @todo Support term_relation, term_synonym table if possible.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_taxonomy_term",
 *   source_module = "taxonomy"
 * )
 */
class DayMigrateTerm extends Term {

  /**
   * {@inheritdoc}
   */
  public function query(): SelectInterface {
    $query = parent::query();
    $query->condition('i18n_tsid', '0', '=');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $tid = $row->getSourceProperty('tid');
    $term_desc_format = $row->getSourceProperty('format');

    if (!empty($term_desc_format) && $term_desc_format == 'filtered_html') {
      $row->setSourceProperty('format', 'restricted_html');
    }

    // Include path alias.
    $query = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias']);
    $query->condition('ua.source', 'taxonomy/term/' . $tid);
    $alias = $query->execute()->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', '/' . $alias);
    }

    parent::prepareRow($row);
  }

}
