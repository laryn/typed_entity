<?php

namespace Drupal\typed_entity\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedEntityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class all wrapped entities should extend from.
 *
 * Wrapped entities are useful to organize the business logic around entities.
 * Any custom logic that applies to an entity should live here, not in hooks.
 * This is not limited to logic related to how an entity is rendered. However
 * business logic related to rendering are a very common use case.
 *
 * @see https://www.lullabot.com/articles/maintainable-code-drupal-wrapped-entities
 */
abstract class WrappedEntityBase implements WrappedEntityInterface {

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected EntityInterface $entity;

  /**
   * The view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface|null
   */
  protected ?EntityViewBuilderInterface $viewBuilder;

  /**
   * The repository manager.
   *
   * @var \Drupal\typed_entity\RepositoryManager|null
   */
  protected ?RepositoryManager $repositoryManager;

  /**
   * WrappedEntityBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
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
   */
  public function owner(): ?WrappedEntityInterface {
    $owner_key = $this->getEntity()->getEntityType()->getKey('owner');
    if (!$owner_key) {
      return NULL;
    }
    return $this->wrapReference($owner_key);
  }

  /**
   * Wraps all the entities referenced by the field name.
   *
   * @param string $field_name
   *   The name of the entity reference field.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped referenced entities.
   */
  public function wrapReferences(string $field_name): array {
    $references = [];
    foreach ($this->getEntity()->{$field_name} as $item) {
      $target_entity = $item->entity;
      if (!$target_entity instanceof EntityInterface) {
        continue;
      }
      $references[] = $this->repositoryManager()->wrap($target_entity);
    }

    return $references;
  }

  /**
   * Wraps the first entity referenced by the field name.
   *
   * @param string $field_name
   *   The name of the entity reference field.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface|null
   *   The wrapped referenced entity.
   */
  public function wrapReference(string $field_name): ?WrappedEntityInterface {
    $target_entity = $this->getEntity()->{$field_name}->entity;
    if (!$target_entity instanceof EntityInterface) {
      return NULL;
    }
    return $this->repositoryManager()->wrap($target_entity);
  }

  /**
   * Lazy initialized of the repository manager.
   *
   * @return \Drupal\typed_entity\RepositoryManager
   *   The repository manager.
   */
  protected function repositoryManager(): RepositoryManager {
    if (!isset($this->repositoryManager)) {
      $this->repositoryManager = typed_entity_repository_manager();
    }
    return $this->repositoryManager;
  }

  /**
   * Lazy initialized of the view builder.
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   *   The repository manager.
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  protected function viewBuilder(): EntityViewBuilderInterface {
    if (!isset($this->viewBuilder)) {
      $entity = $this->getEntity()->getEntityTypeId();
      $entity_type_manager = \Drupal::entityTypeManager();
      $this->viewBuilder = $entity_type_manager->getViewBuilder($entity);
    }
    return $this->viewBuilder;
  }

  /**
   * Sets the view builder.
   *
   * This is mostly here for testing ergonomics.
   *
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $view_builder
   *   The view builder.
   */
  public function setViewBuilder(EntityViewBuilderInterface $view_builder): void {
    $this->viewBuilder = $view_builder;
  }

  /**
   * Sets the repository manager.
   *
   * This is mostly here for testing ergonomics.
   *
   * @param \Drupal\typed_entity\RepositoryManager $repository_manager
   *   The manager.
   */
  public function setRepositoryManager(RepositoryManager $repository_manager): void {
    $this->repositoryManager = $repository_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    return FALSE;
  }

}
