<?php

namespace Drupal\day_migrate\Plugin\migrate\source;

use Drupal\file\Plugin\migrate\source\d7\File;
use Drupal\migrate\Row;

/**
 * Drupal 7 file source (optionally filtered by type) from database.
 *
 * @MigrateSource(
 *   id = "day_migrate_d7_file",
 *   source_module = "file"
 * )
 */
class DayMigrateFile extends File {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    try {
      if (file_exists($uri)) {

      }
    }
    catch (\Exception $e) {
      watchdog_exception('day_migrate', $e);
    }

    return parent::prepareRow($row);
  }

}
