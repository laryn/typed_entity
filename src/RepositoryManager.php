<?php /** @noinspection ALL */

namespace Drupal\typed_entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

/**
 * Repository to wrap entities and negotiate specific repositories.
 */
class RepositoryManager implements EntityWrapperInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * The plugin manager.
   *
   * @var \Drupal\typed_entity\TypedRepositoryPluginManager
   */
  private TypedRepositoryPluginManager $pluginManager;

  /**
   * Caches the deriver base IDs.
   *
   * @var string[]|null
   */
  protected ?array $deriverBaseIds;

  /**
   * RepositoryManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\typed_entity\TypedRepositoryPluginManager $plugin_manager
   *   The plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, TypedRepositoryPluginManager $plugin_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManager = $plugin_manager;
  }

  /**
   * Get all the repositories.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface[]
   *   The repositories.
   *
   * @see get
   */
  public function getAll(): array {
    $definitions = $this->pluginManager->getDefinitions();
    return array_filter(
      array_map([$this, 'get'], array_keys($definitions))
    );
  }

  /**
   * Get a repository.
   *
   * If more than one deriver declares the same pair of entity_type and bundle,
   * the first one found is returned.
   *
   * @param string $repository_id
   *   The repository identifier.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface|null
   *   The repository.
   */
  public function get(string $repository_id): ?TypedRepositoryInterface {
    try {
      $instance = $this->pluginManager->createInstance($repository_id, []);
    }
    catch (PluginException $exception) {
      $instance = array_reduce(
        $this->extractDeriverBaseIds(),
        function ($carry, $base_id) use ($repository_id) {
          return $this->deriverPluginReducer($carry, $base_id, $repository_id);
        }
      );
    }
    if ($instance) {
      return $instance;
    }
    // If we could not find a repository, try with one for the entity type.
    if (strpos($repository_id, TypedRepositoryInterface::ID_PARTS_SEPARATOR) !== FALSE) {
      // This removes the last part of the ID, leaving the deriver and entity
      // type ID intact. `lorem:ipsum.dolor` -> `lorem:ipsum`.
      [$new_repository_id] = explode(TypedRepositoryInterface::ID_PARTS_SEPARATOR, $repository_id, 2);
      return $this->get($new_repository_id);
    }
    return NULL;
  }

  /**
   * Reducer to find the first plugin object based on a deriver base ID.
   *
   * @param object|null $plugin
   *   The plugin object.
   * @param string $base_id
   *   The base ID.
   * @param string $repository_id
   *   The repository ID.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface|null
   *   The plugin object.
   */
  private function deriverPluginReducer(
    ?object $plugin,
    string $base_id,
    string $repository_id
  ): ?TypedRepositoryInterface {
    if ($plugin instanceof TypedRepositoryInterface) {
      return $plugin;
    }
    $plugin_id = sprintf(
      '%s%s%s',
      $base_id,
      PluginBase::DERIVATIVE_SEPARATOR,
      $repository_id
    );
    try {
      $instance = $this->pluginManager->createInstance($plugin_id, []);
      return $instance instanceof TypedRepositoryInterface ? $instance : NULL;
    }
    catch (PluginException $exception) {
      return NULL;
    }
  }

  /**
   * Extracts all the derivers from the list of registered plugins.
   *
   * @return array
   *   The deriver base IDs for the typed repository plugin type.
   */
  protected function extractDeriverBaseIds(): array {
    if (isset($this->deriverBaseIds)) {
      return $this->deriverBaseIds;
    }
    $definitions = $this->pluginManager->getDefinitions();
    $ids = array_keys($definitions);
    $deriver_plugin_ids = array_filter($ids, static function (string $id) {
      return strpos($id, PluginBase::DERIVATIVE_SEPARATOR) !== FALSE;
    });
    $deriver_base_ids = array_map(static function (string $id) {
      [$base] = explode(PluginBase::DERIVATIVE_SEPARATOR, $id, 2);
      return $base;
    }, $deriver_plugin_ids);
    $this->deriverBaseIds = array_unique($deriver_base_ids);
    return $this->deriverBaseIds;
  }

  /**
   * Gets the entity repository based on the entity information and the variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to extract info for.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface|null
   *   The repository for the entity.
   */
  public function repositoryFromEntity(EntityInterface $entity): ?TypedRepositoryInterface {
    return $this->repository($entity->getEntityTypeId(), $entity->bundle());
  }

  /**
   * Gets the entity repository based on the entity information and the variant.
   *
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $bundle
   *   The bundle machine name.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface|null
   *   The repository for the entity.
   */
  public function repository(string $entity_type_id, string $bundle = ''): ?TypedRepositoryInterface {
    $identifier = TypedRepositoryBase::generatePluginId($entity_type_id, $bundle);
    return $this->get($identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface {
    $repository = $this->repositoryFromEntity($entity);
    if (!$repository) {
      return NULL;
    }
    return $repository->wrap($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function wrapMultiple(array $entities): array {
    return array_filter(array_map([$this, 'wrap'], $entities));
  }

}
