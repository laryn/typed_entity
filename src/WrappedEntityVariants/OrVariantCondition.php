<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Configurable variant condition to ensure at least one condition passes.
 */
class OrVariantCondition extends AndVariantCondition {

  /**
   * {@inheritdoc}
   */
  protected function evaluateItem(bool $carry, VariantConditionInterface $condition): bool {
    return $carry || $condition->evaluate();
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Active when at least one of the conditions pass.');
  }

}
