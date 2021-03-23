<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\typed_entity\TypedEntityContext;

/**
 * Configurable variant condition that checks for a given value in a field.
 */
abstract class VariantConditionBase implements VariantConditionInterface {

  use StringTranslationTrait;

  /**
   * Inverse the result of the evaluation.
   *
   * @var bool
   */
  protected $isNegated = FALSE;

  /**
   * The context.
   *
   * @var \Drupal\typed_entity\TypedEntityContext
   */
  protected $context;

  /**
   * VariantConditionBase constructor.
   *
   * @param \Drupal\typed_entity\TypedEntityContext|null $context
   *   The context.
   * @param bool $is_negated
   *   Inverse the result of the evaluation.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function __construct(TypedEntityContext $context = NULL, bool $is_negated = FALSE) {
    $this->isNegated = $is_negated;
    $this->context = $context ?: new TypedEntityContext();
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated(): bool {
    return $this->isNegated;
  }

}
