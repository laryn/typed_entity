<?php

namespace Drupal\typed_entity\Render;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
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
   * @param array $variables
   *   The render array being preprocessed.
   */
  public function preprocess(&$variables): void ;

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
