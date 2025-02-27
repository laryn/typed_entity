<?php

namespace Drupal\typed_entity;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\typed_entity\Annotation\TypedRepository;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;

/**
 * TypedEntityRepository plugin manager.
 */
class TypedRepositoryPluginManager extends DefaultPluginManager {

  /**
   * Constructs TypedEntityRepositoryPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/TypedRepositories',
      $namespaces,
      $module_handler,
      TypedRepositoryInterface::class,
      TypedRepository::class
    );
    $this->alterInfo('typed_repository_info');
    $this->setCacheBackend($cache_backend, 'typed_repository_plugins');
  }

}
