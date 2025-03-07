<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\taxonomy\Plugin\migrate\source\d7\TermTranslation;

/**
 * Provides Drupal 7 taxonomy term locales source plugin.
 *
 * @MigrateSource(
 *   id = "day_d7_taxonomy_term_translation2",
 *   source_module = "i18n"
 * )
 */
class DayMigrateTermTranslation2 extends TermTranslation {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->condition('i18n_tsid', '0', '<>');

    return $query;
  }

  /**SqlContentEntityStorage.php
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $tid = $row->getSourceProperty('tid');

    parent::prepareRow($row);

    $uk_tid = $this->select('taxonomy_term_data', 'ttd')
      ->condition('i18n_tsid', $row->getSourceProperty('i18n_tsid'), '=')
      ->condition('language', 'uk', '=')
      ->fields('ttd', ['tid'])
      ->execute()->fetchField();

    if ($uk_tid) {
      $row->setSourceProperty('custom_tid', $uk_tid);
    }

    $i18n_tsid = $row->getSourceProperty('i18n_tsid');

    if ($i18n_tsid == '0') {
      $row->setSourceProperty('language', 'uk');
    }

    // Include path alias.
    $query = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias']);
    $query->condition('ua.source', 'taxonomy/term/' . $tid);
    $alias = $query->execute()->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', '/' . $alias);
    }
  }

}
