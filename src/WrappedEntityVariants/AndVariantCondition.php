<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Configurable variant condition to ensure all other conditions pass.
 */
class AndVariantCondition extends VariantConditionBase {

  /**
   * The list of variant conditions to check.
   *
   * @var \Drupal\typed_entity\WrappedEntityVariants\VariantConditionInterface[]
   */
  protected $conditions;

  /**
   * AndVariantCondition constructor.
   *
   * @param \Drupal\typed_entity\WrappedEntityVariants\VariantConditionInterface[] $conditions
   *   The list of variant conditions to check.
   * @param string $variant
   *   The FQN of the wrapper class for the variant.
   * @param bool $is_negated
   *   Inverse the result of the evaluation.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function __construct(array $conditions, string $variant = '', bool $is_negated = FALSE) {
    parent::__construct($variant, $is_negated);
    $this->conditions = $conditions;
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    $result = array_reduce($this->conditions, [$this, 'evaluateItem'], FALSE);
    return $this->isNegated() ? !$result : $result;
  }

  /**
   * Evaluates one condition together with the result from previous conditions.
   *
   * Callback for array_reduce().
   *
   * @param bool $carry
   *   The result from previous conditions.
   * @param VariantConditionInterface $condition
   *   The condition to evaluate.
   *
   * @return bool
   *   The condition result, evaluated with the result from previous conditions.
   */
  protected function evaluateItem(bool $carry, VariantConditionInterface $condition): bool {
    return $carry && $condition->evaluate();
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Active when all the conditions pass.');
  }

}
