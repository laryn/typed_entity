<?php

namespace Drupal\typed_entity\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Render\RenderableInterface;
use Drupal\typed_entity\RepositoryManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class all wrapped entities should extend from.
 *
 * Wrapped entities are useful to organize the business logic around entities.
 * Any custom logic that applies to an entity should live here, not in hooks.
 * This is not limited to logic related to how an entity is rendered. However
 * business logic related to rendering are a very common use case.
 *
 * Wrapped entities can be rendered directly in Twig. Print them normally in
 * your template. Put the wrapped entity in the $variables inside of your pre-
 * processor, then use that variable name in Twig. Ex: {{ wrapped_entity }}. Do
 * not forget to set the view mode first.
 *
 * @code
 *   $wrapped_entity->setViewMode('card_medium');
 *   $variables['wrapped_entity'] = $wrapped_entity;
 * @endcode
 *
 * @code
 *   {# Print render the entity with the configured view mode #}
 *   {{ wrapped_entity }}
 * @endcode
 *
 * In your wrapped entity class you can override ::toRenderable to tweak how the
 * entity is rendered.
 *
 * @code
 *   public function toRenderable() {
 *     $build = parent::toRenderable();
 *     // Customize how the entity is rendered.
 *     return ['foo' => ['#markup' => 'Bar is baz.'], 'entity' => $build];
 *   }
 * @endcode
 *
 * @see https://www.lullabot.com/articles/maintainable-code-drupal-wrapped-entities
 */
abstract class WrappedEntityBase implements WrappedEntityInterface, RenderableInterface {

  /**
   * The view mode to render this wrapped entity.
   *
   * @var string
   */
  protected $viewMode = 'default';

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * The repository manager.
   *
   * @var \Drupal\typed_entity\RepositoryManager
   */
  protected $repositoryManager;

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
   */
  public function owner(): ?WrappedEntityInterface {
    $owner_key = $this->getEntity()->getEntityType()->getKey('owner');
    if (!$owner_key) {
      return NULL;
    }
    return $this->wrapReference($owner_key);
  }

  /**
   * Sets the view mode for the entity in preparation to render the wrapper.
   *
   * @param string $view_mode
   *   The view mode.
   */
  public function setViewMode(string $view_mode): void {
    $this->viewMode = $view_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function toRenderable(): array {
    return $this->viewBuilder()->view($this->getEntity(), $this->viewMode);
  }

  /**
   * Wraps all the entities referenced by the field name.
   *
   * @param string $field_name
   *   The name of the entity reference field.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped referenced entities.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
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
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface
   *   The wrapped referenced entity.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
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
    if (!$this->repositoryManager) {
      $this->repositoryManager = \Drupal::service(RepositoryManager::class);
    }
    return $this->repositoryManager;
  }

  /**
   * Lazy initialized of the view builder.
   *
   * @return \Drupal\Core\Entity\EntityViewBuilderInterface
   *   The repository manager.
   */
  protected function viewBuilder(): EntityViewBuilderInterface {
    if (!$this->viewBuilder) {
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

}
