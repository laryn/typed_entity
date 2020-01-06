<?php

namespace Drupal\typed_entity\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\RepositoryManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class all wrapped entities should extend from.
 */
abstract class WrappedEntityBase implements WrappedEntityInterface {

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * WrappedEntityBase constructor.
   */
  public function __construct(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityInterface $entity) {
    return new static($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity(): EntityInterface {
    assert($this->entity instanceof EntityInterface);
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return $this->getEntity()->label();
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function owner(): ?WrappedEntityInterface {
    $owner_key = $this->getEntity()->getEntityType()->getKey('owner');
    if (!$owner_key) {
      return NULL;
    }
    $owner = $this->getEntity()->{$owner_key}->entity;
    if (!$owner instanceof EntityInterface) {
      return NULL;
    }
    $manager = \Drupal::service(RepositoryManager::class);
    assert($manager instanceof RepositoryManager);
    return $manager->wrap($owner);
  }

}
