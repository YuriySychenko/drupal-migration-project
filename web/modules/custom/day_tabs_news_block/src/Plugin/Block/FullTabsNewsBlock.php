<?php

namespace Drupal\day_tabs_news_block\Plugin\Block;

use Drupal\Core\Annotation\Translation;
use Drupal\Core\Block\Annotation\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Create custom short tabs news block.
 *
 * @Block(
 *   id = "day_full_tabs_news_block",
 *   admin_label = @Translation("Day tabs: 14 news item"),
 *   category = @Translation("Custom")
 * )
 */
class FullTabsNewsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * The taxonomy term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $taxonomyStorage;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The current path.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * ShortTabsNewsBlock constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param EntityStorageInterface $node_storage
   * @param EntityStorageInterface $taxonomy_storage
   * @param EntityRepositoryInterface $entity_repository
   * @param DateFormatterInterface $date_formatter
   * @param Connection $database
   * @param LanguageManagerInterface $language_manager
   * @param CurrentPathStack $current_path
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $node_storage, EntityStorageInterface $taxonomy_storage, EntityRepositoryInterface $entity_repository, DateFormatterInterface $date_formatter, Connection $database, LanguageManagerInterface $language_manager, CurrentPathStack $current_path) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->nodeStorage = $node_storage;
    $this->taxonomyStorage = $taxonomy_storage;
    $this->entityRepository = $entity_repository;
    $this->dateFormatter = $date_formatter;
    $this->database = $database;
    $this->languageManager = $language_manager;
    $this->currentPath = $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('node'),
      $container->get('entity_type.manager')->getStorage('taxonomy_term'),
      $container->get('entity.repository'),
      $container->get('date.formatter'),
      $container->get('database'),
      $container->get('language_manager'),
      $container->get('path.current'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {

    $tabs = [
      'actual' => [
        'view' => 'news',
        'display_ids' => [
          'block_1',
          'block_2',
        ],
        'conditions' => [
          'content_types' => [
            'news',
          ],
        ],
        'sorts' => [
          'created' => 'DESC',
        ]
      ],
      'important' => [
        'view' => 'news',
        'display_ids' => [
          'block_1',
          'block_2',
        ],
        'conditions' => [
          'content_types' => [
            'news',
            'openpublish_article',
            'openpublish_blog_post',
          ],
        ],
        'sorts' => [
          'created' => 'DESC',
          'totalcount' => 'ASC',
        ]
      ],
      'popular' => [
        'view' => 'news',
        'conditions' => [
          'content_types' => [
            'news',
            'openpublish_article',
            'openpublish_blog_post',
          ],
          'promote' => FALSE,
        ],
        'sorts' => [
          'totalcount' => 'DESC',
          'created' => 'DESC',
        ]
      ],
    ];

    // Get non-grouped actual tab nodes.
    $actual_tab_nodes = $this->getNonGroupedNodes($tabs, 'actual', TRUE);
    // Get actual tab nodes id for important tab nodes.
    $exclude_actual_tab_nids = [];
    /** @var \Drupal\node\Entity\Node $actual_tab_node */
    foreach ($actual_tab_nodes as $actual_tab_node) {
      $exclude_actual_tab_nids[] = $actual_tab_node->id();
    }
    // Get non-grouped important tab nodes.
    $important_tab_nodes = $this->getNonGroupedNodes($tabs, 'important', TRUE, $exclude_actual_tab_nids);
    // Get non-grouped actual tab nodes.
    $popular_tab_nodes = $this->getNonGroupedNodes($tabs, 'popular', FALSE);

    // Get grouped actual tab nodes.
    $grouped_actual_tab_nodes = $this->getGroupedNodes($actual_tab_nodes);
    // Get grouped important tab nodes.
    $grouped_important_tab_nodes = $this->getGroupedNodes($important_tab_nodes);
    // Get grouped popular tab nodes.
    $grouped_popular_tab_nodes = $this->getGroupedNodes($popular_tab_nodes);

    return [
      '#theme' => 'day_tabs_news_template',
      '#format' => 'full',
      '#language' => $this->languageManager->getCurrentLanguage()->getId(),
      '#is_not_news' => $this->currentPath->getPath() != '/news',
      '#actual' => $grouped_actual_tab_nodes,
      '#important' => $grouped_important_tab_nodes,
      '#popular' => $grouped_popular_tab_nodes,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
    ];
  }

  /**
   * Get non grouped nodes.
   *
   * @param $tabs
   * @param $tab_name
   * @param bool $exclude
   * @param array $exclude_nids
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  protected function getNonGroupedNodes($tabs, $tab_name, bool $exclude = false, array $exclude_nids = []): array {
    $node_storage = $this->nodeStorage;

    if ($exclude) {
      // Get view result.
      $view_results = [];
      foreach ($tabs[$tab_name]['display_ids'] as $display_id) {
        $view = \Drupal\views\Views::getView($tabs[$tab_name]['view']);
        $view->setDisplay($display_id);
        $view->execute();

        $view_results[$display_id] = $view->result;
      }

      // Create exclude nids array.
      foreach ($view_results as $row) {
        foreach ($row as $row_info) {
          $exclude_nids[] = $row_info->_entity->id();
        }
      }
    }

    // Query nodes.
    $query = $this->database->select('node_field_data', 'nfd');

    // Exclude nodes.
    ($exclude && !empty($exclude_nids)) && $query->condition('nfd.nid', $exclude_nids, 'NOT IN');
    // Promote condition.
    (isset($tabs[$tab_name]['conditions']['promote'])) && $query->condition('nfd.promote', $tabs[$tab_name]['conditions']['promote']);

    // Continue query.
    $query->condition('nfd.status', 1)
      ->condition('nfd.type', $tabs[$tab_name]['conditions']['content_types'], 'IN')
      ->condition('nfd.langcode', $this->languageManager->getCurrentLanguage()->getId())
      ->fields('nfd', ['nid']);

    // Continue query.
    $query->range(0, 14);

    // Created sort order for actual and important tabs.
    if ($tab_name == 'actual' || $tab_name == 'important') {
      $query->orderBy('nfd.created', $tabs[$tab_name]['sorts']['created']);
    }

    // Sort by total count.
    if (isset($tabs[$tab_name]['sorts']['totalcount'])) {
      $query->join('node_counter', 'nc', 'nfd.nid = nc.nid');
      $query->orderBy('nc.totalcount', $tabs[$tab_name]['sorts']['totalcount']);
      // Created sort order for popular tab.
      $query->orderBy('nfd.created', $tabs[$tab_name]['sorts']['created']);
    }

    // Get query results
    $query_results = $query->execute()->fetchAll();

    $nids = [];
    foreach ($query_results as $nid) {
      $nids[] = $nid->nid;
    }

    return $node_storage->loadMultiple($nids);
  }

  /**
   * Formation actual news nodes for actual tab.
   *
   * @param $nodes
   * @return array
   */
  protected function getGroupedNodes($nodes): array {
    $grouped = [];

    /** @var \Drupal\node\Entity\Node $node */
    foreach ($nodes as $node) {

      // Formation hot marks for news nodes.
      $field_tag_news = $node->get('field_tag_news')->getValue();

      $tag_news_array = [];
      foreach ($field_tag_news as $tag_news) {
        /** @var \Drupal\taxonomy\Entity\Term $term */
        if ($term = $this->taxonomyStorage->load($tag_news['target_id'])) {
          if ($term->hasField('field_hot_theme')) {
            $tag_news_array[$term->getName()] = $term->get('field_hot_theme')->value != NULL ? $term->get('field_hot_theme')->value : 0;
          }
        }
      }

      // Node created date by format "30 November".
      $node_created_date = $this->dateFormatter->format($node->getCreatedTime(), 'day_custom_date_format_d_f');
      // Node created time by format "22:03".
      $created_time = $this->dateFormatter->format($node->getCreatedTime(), 'day_custom_date_format_h_i');
      // Create link to node.
      /** @var \Drupal\node\Entity\Node $translated_node */
      $translated_node = $this->entityRepository->getTranslationFromContext($node);
      $title = $translated_node->getTitle();

      // Formation of a grouped array of news.
      if (in_array(1, $tag_news_array)) {
        $grouped[$node_created_date][$node->id()]['hot'] = TRUE;
      }
      else {
        $grouped[$node_created_date][$node->id()]['hot'] = FALSE;
      }
      $grouped[$node_created_date][$node->id()]['created_time'] = $created_time;
      $grouped[$node_created_date][$node->id()]['title'] = $title;

    }

    return $grouped;
  }

}
