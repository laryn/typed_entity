<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface;

/**
 * Repository to wrap entities and negotiate specific repositories.
 */
final class RepositoryManager implements EntityWrapperInterface {

  /**
   * The repository collector.
   *
   * @var \Drupal\typed_entity\RepositoryCollector
   */
  private $collector;

  /**
   * RepositoryManager constructor.
   */
  public function __construct(RepositoryCollector $collector) {
    $this->collector = $collector;
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
    $repository = $this->collector->get($identifier);
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
