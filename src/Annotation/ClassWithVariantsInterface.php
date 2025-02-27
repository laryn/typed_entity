<?php

namespace Drupal\typed_entity\Annotation;

use Drupal\typed_entity\TypedEntityContext;

/**
 * Allows class variants to be negotiated with a potential fallback.
 */
interface ClassWithVariantsInterface {

  /**
   * Get the fallback class, if any.
   *
   * @param string $base_class
   *   A FQN for a base class the fallback should extend. Empty if none needed.
   *
   * @return string
   *   The FQN.
   */
  public function getFallback(string $base_class = ''): ?string;

  /**
   * Get the list of registered variants.
   *
   * @param string $base_class
   *   A FQN for a base class the fallback should extend. Empty if none needed.
   *
   * @return array
   *   The class names for the variants.
   */
  public function getVariants(string $base_class = ''): array;

  /**
   * Given a context, negotiate a variant while falling back if none applies.
   *
   * @param \Drupal\typed_entity\TypedEntityContext|null $context
   *   The context object.
   * @param string $base_class
   *   A FQN for a base class the variants should extend.
   *
   * @return string
   *   The FQN for the negotiated variant.
   */
  public function negotiateVariant(TypedEntityContext $context = NULL, string $base_class = ''): ?string;

}
