<?php

namespace Drupal\day_infinite_scroll_next_node\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * FAQ functionality class.
 */
class LoadNextNodeController extends ControllerBase {

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $nodeStorage;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  private $entityRepository;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a new UserMultipleCancelConfirm.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityRepositoryInterface $entity_repository) {
    $this->nodeStorage = $this->entityTypeManager()->getStorage('node');
    $this->languageManager = $this->languageManager();
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository')
    );
  }

  /**
   * Load next node url.
   *
   * @param string $nid
   *   The node id.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   Ajax response.
   */
  public function loadNextNodeUrl(string $nid): JsonResponse {
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();

    // Get NID render.
    if ($nid !== 'undefined') {
      $node = $this->nodeStorage->load($nid);
      $translated_node = $this->entityRepository->getTranslationFromContext($node, $langcode);
      $next_node_url = \Drupal::service('path_alias.manager')
        ->getAliasByPath('/node/' . $translated_node->id());

      // Return response.
      return new JsonResponse($GLOBALS['base_url'] . $next_node_url);
    }
    else {
      return new JsonResponse(FALSE);
    }
  }

}
