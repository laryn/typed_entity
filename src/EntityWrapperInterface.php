<?php

namespace Drupal\typed_entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

/**
 * Wraps entities.
 */
interface EntityWrapperInterface {

  /**
   * Wraps an entity with business logic.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface|null
   *   The wrapped entity.
   */
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface;

  /**
   * Wraps an entities with business logic.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   The entity to wrap.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped entities.
   */
  public function wrapMultiple(array $entities): array;

}
