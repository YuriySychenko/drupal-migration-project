<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;
use Drupal\taxonomy\Entity\Term;

/**
 * Provides Drupal 7 taxonomy term locales source plugin.
 *
 * @MigrateSource(
 *   id = "day_d7_taxonomy_term_translation",
 *   source_module = "i18n"
 * )
 */
class DayMigrateTermTranslation extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('taxonomy_term_data', 'ttd')->fields('ttd');
    $query->addExpression(" (SELECT MIN(tid) FROM taxonomy_term_data ttd2 WHERE ttd2.i18n_tsid=ttd.i18n_tsid) ", "ttd2_tid");
    $query->innerJoin('taxonomy_vocabulary', 'tv', 'ttd.vid=tv.vid');
    if ($this->configuration['bundle']) {
      $query->condition('tv.machine_name', $this->configuration['bundle']);
    }
    $query->condition('ttd.i18n_tsid', 0, '<>');
    $query->addField('tv', 'machine_name');
    $query->orderBy('ttd.i18n_tsid', 'DESC');
    $query->orderBy('ttd.tid', 'ASC');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    if (empty($row->getSourceProperty('bundle'))) {
      $vocabulary = $row->getSourceProperty('machine_name');
    }
    else {
      $vocabulary = $row->getSourceProperty('bundle');
    }
    $language = $row->getSourceProperty('language');
    $tid = $row->getSourceProperty('tid');
    foreach ($this->getFields('taxonomy_term', $vocabulary) as $field_name => $field) {
      $row->setSourceProperty($field_name, $this->getFieldValues('taxonomy_term', $field_name, $tid, NULL, $language));
    }
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'tid' => $this->t('TID of Taxonomy Term.'),
      'name' => $this->t('Name of Taxonomy Term.'),
      'description' => $this->t('Description of Taxonomy Term.'),
      'machine_name' => $this->t('Vocabulary machine name'),
      'language' => $this->t('The target language for this translation.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type' => 'integer',
        'alias' => 'ttd',
      ],
      'language' => [
        'type' => 'string',
        'alias' => 'ttd',
      ],
    ];
  }

}
