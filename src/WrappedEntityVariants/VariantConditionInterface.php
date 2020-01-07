<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

/**
 * Interface for the variant conditions.
 */
interface VariantConditionInterface {

  /**
   * Checks if the condition is negated.
   *
   * @return bool
   *   TRUE if the condition is negated.
   */
  public function isNegated(): bool;

  /**
   * Evaluates the condition.
   *
   * @return bool
   *   TRUE if the condition is fulfilled.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   */
  public function evaluate(): bool;

  /**
   * A human readable summary of the condition. Used for interface purposes.
   *
   * @return string
   *   The summary.
   */
  public function summary(): string;

  /**
   * Gets the FQN of the class for the wrapped entity variant.
   *
   * @return string
   *   The variant class.
   */
  public function variant(): string;

}