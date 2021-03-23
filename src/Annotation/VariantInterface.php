<?php

namespace Drupal\typed_entity\Annotation;

use Drupal\typed_entity\TypedEntityContext;

/**
 * Wrappers and Renderers applying under certain conditions must implement this.
 */
interface VariantInterface {

  /**
   * Checks if a variant should be used in a given context.
   *
   * @param \Drupal\typed_entity\TypedEntityContext $context
   *   The context.
   *
   * @return bool
   *   TRUE if it should be used. FALSE otherwise.
   */
  public static function applies(TypedEntityContext $context): bool;

}
