<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;
use UnexpectedValueException;
use const E_USER_WARNING;

/**
 * Repository to wrap entities and negotiate specific repositories.
 */
class RepositoryManager implements EntityWrapperInterface {

  /**
   * The collected repositories.
   *
   * @var \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface[]
   */
  private $repositories = [];

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * RepositoryCollector constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Adds a repository to the list.
   *
   * @param \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface $repository
   *   The typed entity repository to collect.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $wrapper_class
   *   The FQN for the class that will wrap this entity.
   * @param string $bundle
   *   The bundle name.
   * @param \Drupal\typed_entity\Render\TypedEntityRendererInterface|null $fallback_renderer
   *   The fallback renderer.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @todo: The variant negotiation is still missing.
   */
  public function addRepository(
    TypedEntityRepositoryInterface $repository,
    string $entity_type_id,
    string $wrapper_class,
    string $bundle = '',
    string $fallback_renderer = NULL
  ): void {
    $fallback_renderer = $fallback_renderer ?? '';
    if (empty($entity_type_id)) {
      // We get an empty entity type ID when processing the parent service. We
      // do not want to include it in the collection.
      return;
    }
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    if (empty($bundle)) {
      $this->addAllBundles($repository, $entity_type_id, $wrapper_class, $fallback_renderer);
    }
    else {
      try {
        $repository->init($entity_type, $bundle, $wrapper_class, $fallback_renderer);
      }
      catch (UnexpectedValueException $exception) {
        trigger_error($exception->getMessage(), E_USER_WARNING);
        return;
      }
    }
    $this->repositories[$repository->id()] = $repository;
  }

  /**
   * Adds all the bundles for an entity type using the provided class.
   *
   * @param \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface $repository
   *   The repository to add.
   * @param string $entity_type_id
   *   The entity type ID.
   * @param string $wrapper_class
   *   The class to use for the wrapper.
   * @param \Drupal\typed_entity\Render\TypedEntityRendererInterface|null $fallback_renderer
   *   The fallback renderer.
   */
  private function addAllBundles(
    TypedEntityRepositoryInterface $repository,
    string $entity_type_id,
    string $wrapper_class,
    $fallback_renderer = NULL
  ): void {
    $bundle_info = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo($entity_type_id);
    array_map(function (string $bunde) use ($repository, $entity_type_id, $wrapper_class, $fallback_renderer) {
      $this->addRepository($repository, $entity_type_id, $wrapper_class, $bunde, $fallback_renderer);
    }, array_keys($bundle_info));
  }

  /**
   * Get a repository.
   *
   * @param string $repository_id
   *   The repository identifier.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface|null
   *   The repository.
   */
  public function get(string $repository_id): ?TypedEntityRepositoryInterface {
    return $this->repositories[$repository_id] ?? NULL;
  }

  /**
   * Gets the entity repository based on the entity information and the variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to extract info for.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface|null
   *   The repository for the entity.
   */
  public function repositoryFromEntity(EntityInterface $entity): ?TypedEntityRepositoryInterface {
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
   * @return \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface|null
   *   The repository for the entity.
   */
  public function repository(string $entity_type_id, string $bundle = ''): ?TypedEntityRepositoryInterface {
    $bundle = $bundle ?: $entity_type_id;
    $identifier = implode(
      TypedEntityRepositoryBase::SEPARATOR,
      array_filter([$entity_type_id, $bundle])
    );
    $repository = $this->get($identifier);
    if ($repository === NULL) {
      $message = 'Repository with identifier "' . $identifier . '" not found';
      trigger_error($message, E_USER_WARNING);
      return NULL;
    }
    return $repository;
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface {
    if (!$repository = $this->repositoryFromEntity($entity)) {
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
