<?php

namespace Drupal\typed_entity\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\RepositoryCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Common interface for all the entity wrappers.
 */
interface WrappedEntityInterface {

  /**
   * Gets the wrapped entity.
   *
   * Use this only to pass the entity to core or contrib code that expect an
   * entity. In code you maintain/control you should always pass a wrapper and
   * work with its methods.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The wrapped entity.
   */
  public function getEntity(): EntityInterface;

  /**
   * Instantiates a new instance of this class.
   *
   * This is a factory method that returns a new instance of this class. The
   * factory should pass any needed dependencies into the constructor of this
   * class, but not the container itself. Every call to this method must return
   * a new instance of this class; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this instance should use.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   */
  public static function create(ContainerInterface $container, EntityInterface $entity);

  /**
   * Get the label of the entity.
   *
   * @return string
   */
  public function label(): string;

  /**
   * Gets the owner of the entity.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface|null
   *   The owner.
   */
  public function owner(): ?WrappedEntityInterface;

}
