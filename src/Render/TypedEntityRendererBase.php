<?php

namespace Drupal\typed_entity\Render;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The base renderer.
 *
 * Custom renderers should inherit from this to ensure backwards compatibility
 * with future changes on TypedEntityRendererInterface.
 *
 * If nothing else is selected this might be used.
 */
class TypedEntityRendererBase implements TypedEntityRendererInterface {

  /**
   * The view mode to use with this renderer.
   *
   * Override for each particular renderer if necessary. The context object will
   * take priority when negotiating the view mode.
   *
   * @var ?string
   */
  const VIEW_MODE = 'full';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * TypedEntityRendererBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   *
   * By default render the entity normally.
   */
  public function build(WrappedEntityInterface $wrapped_entity, TypedEntityContext $context): array {
    $entity = $wrapped_entity->getEntity();
    $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    return $view_builder->view($entity, $context['view_mode']);
  }

  /**
   * {@inheritdoc}
   *
   * By default do nothing.
   */
  public function preprocess(array &$variables, WrappedEntityInterface $wrapped_entity): void {}

  /**
   * {@inheritdoc}
   *
   * By default do nothing.
   */
  public function viewAlter(array &$build, WrappedEntityInterface $wrapped_entity, EntityViewDisplayInterface $display): void {}

  /**
   * {@inheritdoc}
   *
   * By default do nothing.
   */
  public function displayBuildAlter(array &$build, WrappedEntityInterface $wrapped_entity, array $context): void {}

  /**
   * {@inheritdoc}
   *
   * By default do nothing.
   */
  public function buildDefaultsAlter(array &$build, WrappedEntityInterface $wrapped_entity, string $view_mode): void {}

  /**
   * {@inheritdoc}
   *
   * By default match based on the declared view mode.
   */
  public static function applies(TypedEntityContext $context): bool {
    $view_mode = $context['view_mode'] ?? NULL;
    return $view_mode === static::VIEW_MODE;
  }

}
