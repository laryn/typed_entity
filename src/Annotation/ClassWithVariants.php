<?php

namespace Drupal\typed_entity\Annotation;

use Drupal\Component\Annotation\AnnotationBase;
use Drupal\typed_entity\TypedEntityContext;

/**
 * Annotation for the wrappers and renderers.
 *
 * @Annotation
 */
class ClassWithVariants extends AnnotationBase implements ClassWithVariantsInterface {

  /**
   * The fallback class.
   *
   * @var string
   */
  public $fallback;

  /**
   * The variants.
   *
   * @var string[]
   */
  public $variants = [];

  /**
   * {@inheritdoc}
   */
  public function get() {
    return $this;
  }

  /**
   * Get the fallback class, if any.
   *
   * @param string $base_class
   *   A FQN for a base class the fallback should extend. Empty if none needed.
   *
   * @return string
   *   The FQN.
   */
  public function getFallback(string $base_class = ''): ?string {
    if (!class_exists($this->fallback)) {
      return NULL;
    }
    if (empty($base_class)) {
      return $this->fallback;
    }
    return is_a($this->fallback, $base_class, TRUE) ? $this->fallback : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariants(string $base_class = ''): array {
    // Only consider the variants implementing the VariantInterface, and
    // extending the (optional) base class.
    return array_filter(
      $this->variants,
      function (string $variant_class) use ($base_class): bool {
        if (!class_exists($variant_class)) {
          return FALSE;
        }
        $interfaces = class_implements($variant_class) ?: [];
        if (!in_array(VariantInterface::class, $interfaces)) {
          return FALSE;
        }
        return empty($base_class) ? TRUE : is_a($variant_class, $base_class, TRUE);
      }
    );
  }

  /**
   * {@inheritdoc}
   */
  public function negotiateVariant(TypedEntityContext $context = NULL, string $base_class = ''): ?string {
    $variants = $this->getVariants($base_class);
    // If there is a base class to further restrict the variants apply it now.
    if (!empty($base_class) && class_exists($base_class)) {
      $variants = array_filter(
        $variants,
        function (string $variant) use ($base_class): bool {
          return is_a($variant, $base_class, TRUE);
        }
      );
    }
    $variant = array_reduce(
      $variants,
      function (string $result, string $variant) use ($context) {
        if (!empty($result)) {
          return $result;
        }
        return call_user_func_array([$variant, 'applies'], [$context]) ? $variant : '';
      },
      ''
    );
    return $variant ?: $this->getFallback($base_class);
  }

}
