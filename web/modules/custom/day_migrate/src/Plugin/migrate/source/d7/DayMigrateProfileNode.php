<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_profile_node",
 *   source_module = "node"
 * )
 */
class DayMigrateProfileNode extends Node {

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $query = parent::query();
    $query->condition('n.type', 'profile');

    return $query;
  }

  /**
   * {@inheritdoc}
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
  }

  /**
   * {@inheritDoc}
   */
  public function handleTranslations(SelectInterface $query) {
    // Ignore the parent conditions to import original nodes with translations.
  }

}
