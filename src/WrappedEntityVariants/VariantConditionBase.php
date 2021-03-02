<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\StringTranslation\StringTranslationTrait;

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
   * The FQN of the wrapper class for the variant.
   *
   * @var string
   */
  protected $variant;

  /**
   * VariantConditionBase constructor.
   *
   * @param string $variant
   *   The FQN of the wrapper class for the variant.
   * @param bool $is_negated
   *   Inverse the result of the evaluation.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function __construct(string $variant = '', bool $is_negated = FALSE) {
    $this->isNegated = $is_negated;
    $this->variant = $variant;
  }

  /**
   * {@inheritdoc}
   */
  public function isNegated(): bool {
    return $this->isNegated;
  }

  /**
   * {@inheritdoc}
   */
  public function variant(): string {
    return $this->variant;
  }

}
