<?php


namespace Drupal\typed_entity\WrappedEntities;


use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\RepositoryCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * Get the label of the entity.
   *
   * @return string
   */
  public function label(): string {
    return $this->getEntity()->label();
  }

  /**
   * Gets the owner of the entity.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface|null
   *   The owner.
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
    $collector = \Drupal::service(RepositoryCollector::class);
    assert($collector instanceof RepositoryCollector);
    return $collector->wrap($owner);
  }

}
