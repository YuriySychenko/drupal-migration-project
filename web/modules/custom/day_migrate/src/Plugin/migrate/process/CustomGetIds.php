<?php

namespace Drupal\day_migrate\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 *
 * Custom plugin get news tags.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "custom_get_ids",
 *   handle_multiples = TRUE
 * )
 */
class CustomGetIds extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $source = $this->configuration['source'];
    $properties = is_string($source) ? [$source] : $source;
    $return = [];
    foreach ($properties as $property) {
      if ($property || (string) $property === '0') {
        $return[] = $row->get($property);
      }
      else {
        $return[] = $value;
      }
    }


    if (is_array($return[0]) && isset($return[0][0])) {
      $return[0] = $return[0][0];
    }

    if (is_string($source)) {
      $this->multiple = is_array($return[0]);
      return $return[0];
    }
    return $return;
  }

}
