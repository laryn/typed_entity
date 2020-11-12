<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\typed_entity\InvalidValueException;

/**
 * Configurable variant condition that checks for a given value in a field.
 */
class FieldValueVariantCondition implements VariantConditionInterface, ContextAwareInterface {

  use ContextAwareTrait;
  use StringTranslationTrait;

  /**
   * Inverse the result of the evaluation.
   *
   * @var bool
   */
  protected $isNegated = FALSE;

  /**
   * Name of the field that contains the data.
   *
   * @var string
   */
  protected $fieldName = '';

  /**
   * The value to check for.
   *
   * @var null
   */
  protected $value = NULL;

  /**
   * The FQN of the wrapper class for the variant.
   *
   * @var string
   */
  protected $variant;

  /**
   * FieldValueVariantCondition constructor.
   *
   * @param string $field_name
   *   Name of the field that contains the data.
   * @param mixed $value
   *   The value to check for.
   * @param string $variant
   *   The FQN of the wrapper class for the variant.
   * @param bool $is_negated
   *   Inverse the result of the evaluation.
   *
   * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
   */
  public function __construct(string $field_name, $value, string $variant, bool $is_negated = FALSE) {
    $this->isNegated = $is_negated;
    $this->fieldName = $field_name;
    $this->value = $value;
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
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function evaluate(): bool {
    // If the value is explicitly set to NULL, use the
    // EmptyFieldVariantCondition instead.
    if ($this->value === NULL) {
      $empty_condition = new EmptyFieldVariantCondition($this->fieldName, $this->variant, $this->isNegated);
      foreach ($this->contexts as $name => $value) {
        $empty_condition->setContext($name, $value);
      }
      return $empty_condition->evaluate();
    }
    $this->validateContext();
    $entity = $this->getContext('entity');
    assert($entity instanceof FieldableEntityInterface);
    // Check if the any of the values for the field match the configured value.
    $values = $entity->get($this->fieldName)->getValue();
    // TODO: inject the field manager for testability.
    $field_manager = \Drupal::service('entity_field.manager');
    assert($field_manager instanceof EntityFieldManager);
    $definition = $field_manager->getFieldStorageDefinitions($entity->getEntityTypeId())[$this->fieldName];
    assert($definition instanceof FieldStorageDefinitionInterface);
    $main_property = $definition->getMainPropertyName();
    $result = array_reduce($values, function ($carry, $value) use ($main_property) {
      return $carry || ($value[$main_property] ?? NULL) == $this->value;
    }, FALSE);
    return $this->isNegated() ? !$result : $result;
  }

  /**
   * {@inheritdoc}
   */
  public function summary(): TranslatableMarkup {
    return $this->t('Active when the %field is %value.', [
      '%field' => $this->fieldName,
      '%value' => $this->value,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function variant(): string {
    return $this->variant;
  }

  /**
   * {@inheritdoc}
   */
  public function validateContext(): void {
    $entity = $this->getContext('entity');
    if (!$entity instanceof FieldableEntityInterface) {
      throw new InvalidValueException('The context for the entity was not fulfilled');
    }
    if (!$entity->hasField($this->fieldName)) {
      $message = sprintf(
        'The entity type "%s" with bundle "%s" does not have a field by name "%s".',
        $entity->getEntityTypeId(),
        $entity->bundle(),
        $this->fieldName
      );
      throw new InvalidValueException($message);
    }
  }

}
