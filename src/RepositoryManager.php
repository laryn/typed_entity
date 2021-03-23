<?php

namespace Drupal\typed_entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;

/**
 * Repository to wrap entities and negotiate specific repositories.
 */
class RepositoryManager implements EntityWrapperInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The plugin manager.
   *
   * @var \Drupal\typed_entity\TypedRepositoryPluginManager
   */
  private $pluginManager;

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
   * Get a repository.
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
      return NULL;
    }
    return $instance instanceof TypedRepositoryInterface
      ? $instance
      : NULL;
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
    $identifier = implode(
      TypedRepositoryBase::SEPARATOR,
      array_filter([$entity_type_id, $bundle])
    );
    return $this->get($identifier);
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface {
    $repository = $this->repositoryFromEntity($entity);
    if (!$repository) {
      // Maybe there is a repository for all bundles.
      $repository = $this->repository($entity->getEntityTypeId());
      if (!$repository) {
        return NULL;
      }
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
