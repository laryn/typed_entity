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
   * The view builder.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected $viewBuilder;

  /**
   * The entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * WrappedEntityBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $entity_view_builder
   *   The view builder.
   */
  public function __construct(EntityInterface $entity, EntityViewBuilderInterface $entity_view_builder) {
    $this->entity = $entity;
    $this->viewBuilder = $entity_view_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityInterface $entity) {
    $entity_view_builder = $container->get('entity_type.manager')
      ->getViewBuilder($entity->getEntityTypeId());
    return new static($entity, $entity_view_builder);
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
    return $this->viewBuilder->view($this->getEntity(), $this->viewMode);
  }

}
