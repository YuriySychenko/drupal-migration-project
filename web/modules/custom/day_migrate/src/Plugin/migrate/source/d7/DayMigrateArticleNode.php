<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_article_node",
 *   source_module = "node"
 * )
 */
class DayMigrateArticleNode extends Node {

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $query = parent::query();
    $query->condition('n.type', 'openpublish_article');
    $query->where('YEAR(FROM_UNIXTIME(n.created)) = :created', array(':created' => 2021));

    return $query;
  }

  /**
   * {@inheritdoc}
   *
   * @noinspection PhpFullyQualifiedNameUsageInspection
   * @throws \Exception
   */
  public function prepareRow(Row $row) {
    $schema = \Drupal::database()->schema();
    $nid = $row->getSourceProperty('nid');
    $tnid = $row->getSourceProperty('tnid');
    $language = $row->getSourceProperty('language');

    try {
      $row->setSourceProperty('vid', NULL);
    } catch (\Exception $e) {
      watchdog_exception('day_migrate', $e);
    }

    parent::prepareRow($row);

    // Here checking if in map table exists a similar node.
    // This is necessary to prevent node overwriting.
    // If that is, then set to nid for tnid.
    if ($schema->tableExists('migrate_map_day_d7_profile_content')) {
      $query_map = \Drupal::database()->select('migrate_map_day_d7_profile_content', 'mmap')
        ->fields('mmap', ['destid1', 'destid2'])
        ->condition('mmap.destid1', $tnid)
        ->condition('mmap.destid2', $language);
      $exist_node = $query_map->execute()->fetchField();

      if ($exist_node) {
        $row->setSourceProperty('tnid', $nid);
      }
    }

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
    // Ignore the parent conditions to import original nodes with translations.
  }

}
