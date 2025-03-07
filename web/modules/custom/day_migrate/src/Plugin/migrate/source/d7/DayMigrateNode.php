<?php

namespace Drupal\day_migrate\Plugin\migrate\source\d7;

use Drupal\Core\Database\Query\SelectInterface;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\Node;

/**
 * Drupal 7 node source from database.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_node",
 *   source_module = "node"
 * )
 */
class DayMigrateNode extends Node {

  /**
   * {@inheritDoc}
   */
  public function query(): SelectInterface {
    $query = parent::query();
    $query->condition('n.type', 'aphorism');

    return $query;
  }

  /**
   * {@inheritdoc}
   *
   * @noinspection PhpFullyQualifiedNameUsageInspection
   */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');

    try {
      $row->setSourceProperty('vid', NULL);
    }
    catch (\Exception $e) {
      watchdog_exception('day_migrate', $e);
    }

    // Include path alias.
    $query = $this->select('url_alias', 'ua')
      ->fields('ua', ['alias']);
    $query->condition('ua.source', 'node/' . $nid);
    $alias = $query->execute()->fetchField();
    if (!empty($alias)) {
      $row->setSourceProperty('alias', '/' . $alias);
    }

    parent::prepareRow($row);
  }

  /**
   * {@inheritDoc}
   */
  public function handleTranslations(SelectInterface $query) {
    // Ignore the parent conditions to import original node with translations.
  }

}
