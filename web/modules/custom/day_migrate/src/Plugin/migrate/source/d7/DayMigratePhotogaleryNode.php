<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_photogalery_node",
 *   source_module = "node"
 * )
 */
class DayMigratePhotogaleryNode extends Node {

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $query = parent::query();
    $query->condition('n.type', 'photogalery');

    return $query;
  }

  /**
   * {@inheritdoc}
   *
   * @noinspection PhpFullyQualifiedNameUsageInspection
   * @throws \Exception
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
    $tnid = $row->getSourceProperty('tnid');

    try {
      $row->setSourceProperty('vid', NULL);
    } catch (\Exception $e) {
      watchdog_exception('day_migrate', $e);
    }

    parent::prepareRow($row);

    // Include path alias.
    $query = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias']);
    $query->condition('ua.source', 'node/' . $nid);
    $alias = $query->execute()->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', '/' . $alias);
    }

    // Include related news.
    $field_related_news_node = 'field_op_related_nref';

    $table_field_related_news_node = 'field_data_' . $field_related_news_node;
    $query = $this->select($table_field_related_news_node, 'tfrn')
      ->fields('tfrn')
      ->condition('entity_type', 'node')
      ->condition('entity_id', $tnid)
      ->condition('deleted', 0);

    $related_news_ids = [];
    foreach ($query->execute() as $query_row) {
      foreach ($query_row as $key => $value) {
        $delta = $query_row['delta'];
        if (strpos($key, $field_related_news_node) === 0) {
          $related_news_ids[$delta]['target_id'] = $value;
        }
      }
    }


    if (!empty($related_news_ids)) {
      $row->setSourceProperty('field_op_related_nref', $related_news_ids);
    }

  }

  /**
   * {@inheritDoc}
   */
  public function handleTranslations(SelectInterface $query) {
    // Ignore the parent conditions to import original node with translations.
  }

}
