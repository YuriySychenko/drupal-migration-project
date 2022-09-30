<?php

namespace Drupal\day_custom_breadcrumbs\Breadcrumb;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\ParamConverter\ParamNotConvertedException;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Path\PathMatcherInterface;
use Drupal\Core\PathProcessor\InboundPathProcessorInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Routing\RequestContext;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Link;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\node\Entity\Node;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

/**
 * Custom Page Breadcrumb Builder.
 */
class CustomPageBreadcrumbBuilder implements BreadcrumbBuilderInterface {

  use StringTranslationTrait;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Drupal\Core\Entity\EntityRepositoryInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Drupal\Core\Path\CurrentPathStack definition.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;


  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Drupal\language\ConfigurableLanguageManagerInterface definition.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The router request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $context;

  /**
   * The menu link access service.
   *
   * @var \Drupal\Core\Access\AccessManagerInterface
   */
  protected $accessManager;

  /**
   * The dynamic router service.
   *
   * @var \Symfony\Component\Routing\Matcher\RequestMatcherInterface
   */
  protected $router;

  /**
   * The inbound path processor.
   *
   * @var \Drupal\Core\PathProcessor\InboundPathProcessorInterface
   */
  protected $pathProcessor;

  /**
   * The title resolver.
   *
   * @var \Drupal\Core\Controller\TitleResolverInterface
   */
  protected $titleResolver;

  /**
   * The patch matcher service.
   *
   * @var \Drupal\Core\Path\PathMatcherInterface
   */
  protected $pathMatcher;

  /**
   * The user storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $userStorage;

  /**
   * The taxonomy storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  private $taxonomyStorage;

  /**
   * The current route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The route admin context to determine whether a route is an admin one.
   *
   * @var \Drupal\Core\Routing\AdminContext
   */
  protected $adminContext;

  /**
   * Constructs a new realtyBreadcrumb object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Routing\AdminContext $admin_context
   *   The route admin context service.
   * @param \Drupal\Core\Path\CurrentPathStack $path_current
   *   The current path.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently authenticated user.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $language_manager
   *   The configurable language manager.
   * @param \Drupal\Core\Routing\RequestContext $context
   *   The router request context.
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Symfony\Component\Routing\Matcher\RequestMatcherInterface $router
   *   The router doing the actual routing.
   * @param \Drupal\Core\PathProcessor\InboundPathProcessorInterface $path_processor
   *   The path processor.
   * @param \Drupal\Core\Controller\TitleResolverInterface $title_resolver
   *   The title resolver service.
   * @param \Drupal\Core\Path\PathMatcherInterface $path_matcher
   *   The path matcher.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   *   The current route matcher.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface           $entity_type_manager,
    ConfigFactoryInterface               $config_factory,
    EntityRepositoryInterface            $entity_repository,
    AdminContext                         $admin_context,
    CurrentPathStack                     $path_current,
    AliasManagerInterface                $alias_manager,
    AccountInterface                     $current_user,
    ConfigurableLanguageManagerInterface $language_manager,
    RequestContext                       $context,
    AccessManagerInterface               $access_manager,
    RequestMatcherInterface              $router,
    InboundPathProcessorInterface        $path_processor,
    TitleResolverInterface               $title_resolver,
    PathMatcherInterface                 $path_matcher,
    CurrentRouteMatch                    $current_route_match
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->userStorage = $entity_type_manager->getStorage('user');
    $this->taxonomyStorage = $entity_type_manager->getStorage('taxonomy_term');
    $this->adminContext = $admin_context;
    $this->configFactory = $config_factory;
    $this->entityRepository = $entity_repository;
    $this->currentPath = $path_current;
    $this->aliasManager = $alias_manager;
    $this->currentUser = $current_user;
    $this->languageManager = $language_manager;
    $this->context = $context;
    $this->accessManager = $access_manager;
    $this->router = $router;
    $this->pathProcessor = $path_processor;
    $this->titleResolver = $title_resolver;
    $this->pathMatcher = $path_matcher;
    $this->currentRouteMatch = $current_route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $attributes) {
    $parameters = $attributes->getParameters()->all();

    // Disable override breadcrumbs on admin layout.
    if ($this->adminContext->isAdminRoute()) {
      return FALSE;
    }

    // Determine if the current page is a node page.
    if (isset($parameters['node']) && !empty($parameters['node'])) {
      return TRUE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();

    // Get the node for the current page.
    /** @var Node $node */
    $node = $route_match->getParameter('node');


    if ($node && is_object($node)) {
      switch ($node->bundle()) {
        case 'news':
        case 'openpublish_article':
        case 'openpublish_blog_post':
        case 'longread_article':
        case 'photogalery':
        case 'openpublish_video':
        case 'profile':
          $this->applyMainEntitiesBreadcrumb($breadcrumb, $node);
          break;
        case 'page':
        case 'section_front':
          $this->applyBreadcrumbByPath($breadcrumb, $node);
          break;
        case 'product':
          $this->applyBreadcrumbProduct($breadcrumb, $node);
          break;
      }
      $breadcrumb->addCacheContexts(['route']);
    }
    else {
      if ($route_match->getRouteName() == 'entity.taxonomy_term.canonical'
        && $route_match->getParameter('taxonomy_term') instanceof TermInterface) {
        $this->applyTaxonomyBreadcrumbs($breadcrumb);
      }
      elseif ($route_match->getRouteName() == 'system.404') {
        $this->applyBreadcrumb404($breadcrumb);
      }
      elseif ($route_match->getRouteName() == 'system.403') {
        $this->applyBreadcrumb403($breadcrumb);
      }
      elseif ($route_match->getRouteName() == 'view.search_via_search_api.page_1') {
        $this->applyBreadcrumbSearch($breadcrumb);
      }
      else {
        $this->applyBreadcrumbByPath($breadcrumb);
      }
      $breadcrumb->addCacheContexts(['route']);
    }

    return $breadcrumb;
  }

  /**
   * Build Breadcrumbs for the all main content type nodes.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   * @param Node|null $node
   *   Current node instance.
   */
  private function applyMainEntitiesBreadcrumb(Breadcrumb &$breadcrumb, Node $node): void {
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    switch ($node->bundle()) {
      case 'news':
        $breadcrumb->addLink(Link::createFromRoute($this->t('News'),
          'view.news.page_1',
        ));
        break;
      case 'openpublish_article':
      case 'longread_article':
        $breadcrumb->addLink(Link::createFromRoute($this->t('Newspaper'),
          'view.main_section_newspaper.page_2',
        ));
        break;
      case 'openpublish_blog_post':
      case 'profile':
        $breadcrumb->addLink(Link::createFromRoute($this->t('Blogs'),
          'view.authors_blogs.page_1',
        ));
        break;
      case 'photogalery':
        $breadcrumb->addLink(Link::createFromRoute($this->t('Photoreports'),
          'entity.node.canonical',
          ['node' => '784807']
        ));
        break;
      case 'openpublish_video':
        $breadcrumb->addLink(Link::createFromRoute($this->t('Video'),
          'entity.node.canonical',
          ['node' => '120']
        ));
        break;
    }

    // Get main tags.
    if ($node->hasField('field_tag_news')) {
      if (!$node->field_tag_news->isEmpty()) {
        $field_tag_news = $node->get('field_tag_news')->getValue();

        foreach ($field_tag_news as $tag_news) {
          /** @var Term $term */
          if ($term = $this->taxonomyStorage->load($tag_news['target_id'])) {
            $translation_term = $this->entityRepository->getTranslationFromContext($term);
            // Check if this term is main tag
            if ($translation_term->hasField('field_main_tag')) {
              $main_tag = $translation_term->get('field_main_tag')->value;

              if ($main_tag) {
                $breadcrumb->addLink(Link::createFromRoute($translation_term->label(),
                  'entity.taxonomy_term.canonical',
                  ['taxonomy_term' => $translation_term->id()]
                ));
              }
            }
          }
        }
      }
    }

    // Get category.
    $categories = [
      'field_op_section_term',
      'field_op_blogpost_blog'
    ];

    foreach ($categories as $category) {
      if ($node->hasField($category)) {
        if (!$node->{$category}->isEmpty()) {
          $article_section_term_id = $node->get($category)->getValue()[0]['target_id'];
          /** @var Term $term */
          if ($term = $this->taxonomyStorage->load($article_section_term_id)) {
            $translation_term = $this->entityRepository->getTranslationFromContext($term);
            $breadcrumb->addLink(Link::createFromRoute($translation_term->label(),
              'entity.taxonomy_term.canonical',
              ['taxonomy_term' => $translation_term->id()]
            ));
          }
        }
      }
    }

    $breadcrumb->addLink(Link::createFromRoute($node->label(), '<none>'));
  }


  /**
   * Build Breadcrumbs for the add_advert content type node.
   *
   * @param Breadcrumb $breadcrumb
   * @param Node $node
   */
  private function applyBreadcrumbProduct(Breadcrumb &$breadcrumb, Node $node): void {
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));

    if (!$node->field_category_product->isEmpty()) {
      $categoryTerm = $this->entityRepository->getTranslationFromContext($node->get('field_category_product')->entity);
      if ($this->getParentTerm($categoryTerm)) {
        if ($parentTerm = $this->entityRepository->getTranslationFromContext($this->getParentTerm($categoryTerm))) {
          if ($rootTerm = $this->getParentTerm($parentTerm)) {
            $breadcrumb->addLink(Link::createFromRoute($rootTerm->label(),
              'entity.taxonomy_term.canonical',
              ['taxonomy_term' => $rootTerm->id()]
            ));
          }

          $breadcrumb->addLink(Link::createFromRoute($parentTerm->label(),
            'entity.taxonomy_term.canonical',
            ['taxonomy_term' => $parentTerm->id()]
          ));
        }
      }
    }
    $breadcrumb->addLink(Link::createFromRoute($categoryTerm->label(),
      'entity.taxonomy_term.canonical',
      ['taxonomy_term' => $categoryTerm->id()]
    ));
    $breadcrumb->addLink(Link::createFromRoute($node->label(), '<none>'));
  }

  /**
   * Build Breadcrumbs for 404 page.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   */
  private function applyBreadcrumb404(Breadcrumb &$breadcrumb): void {
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('404'), '<none>'));
  }

  /**
   * Build Breadcrumbs for 403 page.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   */
  private function applyBreadcrumb403(Breadcrumb &$breadcrumb): void {
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('403'), '<none>'));
  }

  /**
   * Build Breadcrumbs for search page.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   */
  private function applyBreadcrumbSearch(Breadcrumb &$breadcrumb): void {
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addLink(Link::createFromRoute($this->t('Search'), '<none>'));
  }

  /**
   * Matches a path in the router.
   *
   * @param string $path
   *   The request path with a leading slash.
   * @param array $exclude
   *   An array of paths or system paths to skip.
   *
   * @return \Symfony\Component\HttpFoundation\Request
   *   A populated request object or NULL if the path couldn't be matched.
   */
  protected function getRequestForPath($path, array $exclude) {
    if (!empty($exclude[$path])) {
      return NULL;
    }
    // @todo Use the RequestHelper once https://www.drupal.org/node/2090293 is
    //   fixed.
    $request = Request::create($path);
    // Performance optimization: set a short accept header to reduce overhead in
    // AcceptHeaderMatcher when matching the request.
    $request->headers->set('Accept', 'text/html');
    // Find the system path by resolving aliases, language prefix, etc.
    $processed = $this->pathProcessor->processInbound($path, $request);
    if (empty($processed) || !empty($exclude[$processed])) {
      // This resolves to the front page, which we already add.
      return NULL;
    }
    $this->currentPath->setPath($processed, $request);
    // Attempt to match this path to provide a fully built request.
    try {
      $request->attributes->add($this->router->matchRequest($request));
      return $request;
    } catch (ParamNotConvertedException $e) {
      return NULL;
    } catch (ResourceNotFoundException $e) {
      return NULL;
    } catch (MethodNotAllowedException $e) {
      return NULL;
    } catch (AccessDeniedHttpException $e) {
      return NULL;
    }
  }

  /**
   * Build Breadcrumbs from current path parameters.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   * @param Node|null $node
   *   Current node instance.
   */
  private function applyBreadcrumbByPath(Breadcrumb &$breadcrumb, Node $node = NULL) {
    $langcode = $this->languageManager->getCurrentLanguage()->getId();
    $links = [];
    // Add the url.path.parent cache context. This code ignores the last path
    // part so the result only depends on the path parents.
    $breadcrumb->addCacheContexts(['url.path.parent', 'url.path.is_front']);

    // Do not display a breadcrumb on the frontpage.
    if ($this->pathMatcher->isFrontPage()) {
      return;
    }

    if ($node) {
      $url = Url::fromRoute('<none>');
      $links[] = new Link($node->label(), $url);
    }

    // General path-based breadcrumbs. Use the actual request path, prior to
    // resolving path aliases, so the breadcrumb can be defined by simply
    // creating a hierarchy of path aliases.
    $path = trim($this->context->getPathInfo(), '/');
    $path_elements = explode('/', $path);
    $exclude = [];
    // Don't show a link to the front-page path.
    $front = $this->configFactory->get('system.site')->get('page.front');
    $exclude[$front] = TRUE;
    // /user is just a redirect, so skip it.
    $exclude['/user'] = TRUE;
    $exclude['/node'] = TRUE;
    if (in_array($langcode, $path_elements)) {
      array_splice($path_elements, 0, 1);
    }
    foreach ($path_elements as $path_element) {
      // Copy the path elements for up-casting.
      $route_request = $this->getRequestForPath('/' . $path_element, $exclude);
      if ($route_request) {
        $route_match = RouteMatch::createFromRequest($route_request);
        $access = $this->accessManager->check($route_match, $this->currentUser, NULL, TRUE);
        // The set of breadcrumb links depends on the access result, so merge
        // the access result's cacheability metadata.
        $breadcrumb = $breadcrumb->addCacheableDependency($access);
        if ($access->isAllowed()) {
          $title = $this->titleResolver->getTitle($route_request, $route_match->getRouteObject());
          if (!isset($title)) {
            // Fallback to using the raw path component as the title if the
            // route is missing a _title or _title_callback attribute.
            $title = str_replace(['-', '_'], ' ', ucfirst(end($path_elements)));
            $title = $this->t($title)->render();
          }
          if ($path_element === end($path_elements) || $path === 'checkout/3/order_information') {
            $url = Url::fromUri('route:<nolink>');
          }
          else {
            $url = Url::fromRouteMatch($route_match);
          }
          if (!empty($links)) {
            foreach ($links as $link) {
              if ($link->getText() !== $title) {
                $links[] = new Link($title, $url);
              }
            }
          }
          else {
            $links[] = new Link($title, $url);
          }
        }
      }
    }

    // Add the Home link.
    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $breadcrumb->setLinks(array_reverse($links));
  }

  /**
   * Build taxonomy breadcrumbs.
   *
   * @param Breadcrumb $breadcrumb
   *   Breadcrumb instance.
   */
  private function applyTaxonomyBreadcrumbs(Breadcrumb &$breadcrumb) {
    $breadcrumb = new Breadcrumb();

    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    /** @var TermInterface $term */
    $term = $this->currentRouteMatch->getParameter('taxonomy_term');

    $current_path = $this->currentPath->getPath();
    $current_path_alias = $this->aliasManager->getAliasByPath($current_path);


    if (strpos($current_path_alias, 'news') !== false) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('News'),
        'view.news.page_1',
      ));
    }
    elseif (strpos($current_path_alias, 'article') !== false) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('Newspaper'),
        'view.main_section_newspaper.page_2',
      ));
    }
    elseif (strpos($current_path_alias, 'blog') !== false) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('Blogs'),
        'view.authors_blogs.page_1',
      ));
    }
    elseif (strpos($current_path_alias, 'arhiv') !== false) {
      $breadcrumb->addLink(Link::createFromRoute($this->t('Archive'),
        'view.last_newspapers_block.page_1',
      ));
    }

    // Breadcrumb needs to have terms cacheable metadata as a cacheable
    // dependency even though it is not shown in the breadcrumb because e.g. its
    // parent might have changed.
    $breadcrumb->addCacheableDependency($term);
    // @todo This overrides any other possible breadcrumb and is a pure
    //   hard-coded presumption. Make this behavior configurable per
    //   vocabulary or term.
    $parents = $this->taxonomyStorage->loadAllParents($term->id());
    $parents = array_reverse($parents);

    foreach ($parents as $term) {
      $term = $this->entityRepository->getTranslationFromContext($term);
      $last_term = $this->entityRepository->getTranslationFromContext(end($parents));
      $breadcrumb->addCacheableDependency($term);
      if ($term === $last_term) {
        $breadcrumb->addLink(Link::createFromRoute($term->getName(), '<none>'));
      }
      else {
        $breadcrumb->addLink(Link::createFromRoute($term->getName(), 'entity.taxonomy_term.canonical', ['taxonomy_term' => $term->id()]));
      }
    }
  }

  /**
   * Get parent taxonomy term.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   Taxonomy Term Entity.
   *
   * @return mixed
   *   False if no parent.
   */
  protected function getParentTerm(Term $term) {
    $termParents = $this->taxonomyStorage->loadParents($term->id());
    if ($termParents) {
      return reset($termParents);
    }
    return FALSE;
  }

}
