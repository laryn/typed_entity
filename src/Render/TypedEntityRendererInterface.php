<?php

namespace Drupal\typed_entity\Render;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;

interface TypedEntityRendererInterface {

  /**
   * Returns a render array representation of the wrapped entity.
   *
   * @param \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface $wrapped_entity
   *   The wrapped entity to render.
   * @param \Drupal\typed_entity\Render\TypedEntityRenderContext $context
   *   The context this entity is rendered in. This contains arbitrary
   *   information on how to render the entity. Special keys:
   *     - 'view_mode': The view mode to use to render the entity. Leave it
   *       empty to use a static value declared in the renderer.
   *
   * @return mixed[]
   *   A render array.
   */
  public function build(WrappedEntityInterface $wrapped_entity, TypedEntityRenderContext $context): array;

  /**
   * Alter the render array for the associated entity.
   *
   * The children added here will be rendered without any changes necessary in
   * the template. If you want to pass raw variables to the template use
   * ::preprocess.
   *
   * @param array $build
   *   The render array being preprocessed.
   * @param \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface $wrapped_entity
   *   The wrapped entity.
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   The entity view display.
   *
   * @see ::preprocess()
   */
  public function viewAlter(array &$build, WrappedEntityInterface $wrapped_entity, EntityViewDisplayInterface $display): void;

  /**
   * Custom preprocessing for the renderer.
   *
   * @param array $variables
   *   The render array passed by reference.
   * @param \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface $wrapped_entity
   *   The typed entity being processed. It is only here for context. It is not
   *   recommended to extract data from the entity directly without passing it
   *   through the render pipeline.
   */
  public function preprocess(array &$variables, WrappedEntityInterface $wrapped_entity): void;

  /**
   * Checks if a renderer should be used in a given context.
   *
   * @param \Drupal\typed_entity\Render\TypedEntityRenderContext $context
   *   The render context.
   *
   * @return bool
   *   TRUE if it should be used. FALSE otherwise.
   */
  public static function applies(TypedEntityRenderContext $context): bool;

}