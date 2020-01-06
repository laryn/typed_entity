<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Component\Assertion\Inspector;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

interface TypedEntityRepositoryInterface {

  /**
   * Wraps an entity with business logic.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface
   *   The wrapped entity.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   */
  public function wrap(EntityInterface $entity): WrappedEntityInterface;

  /**
   * Wraps an entities with business logic.
   *
   * @param \Drupal\Core\Entity\EntityInterface[] $entities
   *   The entity to wrap.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped entities.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   */
  public function wrapMultiple(array $entities): array;

  /**
   * Initialize the repository with the parameters in the service container.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type for this repository.
   * @param string $bundle
   *   The bundle name.
   * @param string $wrapper_class
   *   The class to instantiate when creating wrapped entities using this
   *   repository.
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class): void;

  /**
   * Gets a query to start finding items.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The query to execute.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getQuery(): QueryInterface;

  /**
   * Build the repository identifier.
   *
   * @return string
   *   The identifier.
   */
  public function id(): string;

}
