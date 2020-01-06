<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;

/**
 * Collects the repositories and negotiates from a loaded entity.
 */
final class RepositoryCollector implements EntityWrapperInterface {

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
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *
   * @todo: The variant negotiation is still missing.
   */
  public function addRepository(
    TypedEntityRepositoryInterface $repository,
    string $entity_type_id,
    string $wrapper_class,
    string $bundle = ''
  ) {
    if (empty($entity_type_id)) {
      // We get an empty entity type ID when processing the parent service. We
      // do not want to include it in the collection.
      return;
    }
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
    $repository->init($entity_type, $bundle, $wrapper_class);
    $this->repositories[$repository->id()] = $repository;
  }

  /**
   * Gets the entity repository based on the entity information and the variant.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to extract info for.
   *
   * @return \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface
   *   The repository for the entity.
   *
   * @throws \Drupal\typed_entity\RepositoryNotFoundException
   *   When the repository was not found.
   *
   * @todo: The variant negotiation is still missing.
   */
  public function repositoryFromEntity(EntityInterface $entity): TypedEntityRepositoryInterface {
    $identifier = implode(
      TypedEntityRepositoryBase::SEPARATOR,
      array_filter([$entity->getEntityTypeId(), $entity->bundle()])
    );
    $repository = $this->repositories[$identifier];
    if (empty($repository)) {
      $message = 'Repository with identifier "' . $identifier . '" not found';
      throw new RepositoryNotFoundException($message);
    }
    return $repository;
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): WrappedEntityInterface {
    return $this->repositoryFromEntity($entity)->wrap($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function wrapMultiple(array $entities): array {
    return array_map([$this, 'wrap'], $entities);
  }

}
