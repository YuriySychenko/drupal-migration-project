<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "migrate_aphorism",
 *   source_module = "node"
 * )
 */
class MigrateAphorism extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {

    // this queries the built-in metadata, but not the body, tags, or images.
    $query = $this->select('node', 'n')
      ->condition('n.type', 'aphorism')
      ->fields('n', [
        'nid',
        'tnid',
        'type',
        'uid',
        'language',
        'title',
        'status',
        'created',
        'changed',
        'comment',
        'promote',
        'sticky',
        'tnid',
        'translate',
      ]);
    $query->orderBy('nid');

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [
      'nid' => $this->t('Node ID'),
      'tnid' => $this->t('The translation set id for this node'),
      'node_uid' => $this->t('Node authored by (uid)'),
      'type' => $this->t('Content type'),
      'language' => $this->t('Language (fr, en, ...)'),
      'title' => $this->t('Title'),
      'status' => $this->t('Published'),
      'revision_uid' => $this->t('Revision authored by (uid)'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'promote' => $this->t('Promoted to front page'),
      'sticky' => $this->t('Sticky at top of lists'),
      'revision' => $this->t('Create new revision'),
      'timestamp' => $this->t('The timestamp the latest revision of this node was created.'),
      'aphorism_author' => $this->t('Aphorism author'),
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    // Aphorism author field set value.
    $field_aphorism_author_value = $this->select('field_data_field_aphorism_author', 'auth')
      ->fields('auth', ['field_aphorism_author_value'])
      ->condition('auth.entity_id', $nid)
      ->execute()->fetchField();
    if (!empty($field_aphorism_author_value)) {
      $row->setSourceProperty('aphorism_author', $field_aphorism_author_value);
    }

    // Make sure we always have a translation set.
//    if ($row->getSourceProperty('tnid') == 0) {
//      $row->setSourceProperty('tnid', $row->getSourceProperty('nid'));
//    }

    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';
    return $ids;
  }

}
