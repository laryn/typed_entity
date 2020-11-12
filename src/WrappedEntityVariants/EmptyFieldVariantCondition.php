<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Configurable variant condition that checks whether a field is empty.
 */
class EmptyFieldVariantCondition extends FieldValueVariantCondition {

  /**
   * EmptyFieldValueVariantCondition constructor.
   *
   * @param string $field_name
   *   Name of the field that contains the data.
   * @param string $variant
   *   The FQN of the wrapper class for the variant.
   * @param bool $is_negated
   *   Inverse the result of the evaluation.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function __construct(string $field_name, string $variant, bool $is_negated = FALSE) {
    parent::__construct($field_name, NULL, $variant, $is_negated);
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate(): bool {
    $this->validateContext();
    $entity = $this->getContext('entity');
    assert($entity instanceof FieldableEntityInterface);
    $is_empty = $entity->get($this->fieldName)->isEmpty();
    return $this->isNegated() ? !$is_empty : $is_empty;
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Active when "%field" is empty.', [
      '%field' => $this->fieldName,
    ]);
  }

}
