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
   * {@inheritdoc}
   */
  public function getFallback(): ?string {
    return class_exists($this->fallback) ? $this->fallback : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariants(): array {
    // Only consider the variants implementing the VariantInterface.
    return array_filter($this->variants, function (string $variant_class): bool {
      return class_exists($variant_class) && in_array(VariantInterface::class, class_implements($variant_class) ?: []);
    });
  }

  /**
   * {@inheritdoc}
   */
  public function negotiateVariant(TypedEntityContext $context = NULL, string $base_class = ''): ?string {
    $variants = $this->getVariants();
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
    return $variant ?: $this->getFallback();
  }

}
