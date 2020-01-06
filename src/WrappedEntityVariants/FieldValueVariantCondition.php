<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

use Drupal\Core\Entity\EntityFieldManager;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\typed_entity\InvalidValueException;

class FieldValueVariantCondition implements VariantConditionInterface, ContextAwareInterface {

  use ContextAwareTrait;
  use StringTranslationTrait;

  protected $isNegated = FALSE;
  protected $fieldName = '';
  protected $value = NULL;
  protected $variant;

  /**
   * FieldValueVariantCondition constructor.
   *
   * @param bool $is_negated
   * @param string $field_name
   * @param null $value
   * @param $variant
   */
  public function __construct(string $field_name, $value, $variant, bool $is_negated = FALSE) {
    $this->isNegated = $is_negated;
    $this->fieldName = $field_name;
    $this->value = $value;
    $this->variant = $variant;
  }

  public function isNegated(): bool {
    return $this->isNegated;
  }

  public function evaluate(): bool {
    $this->validateContext();
    $entity = $this->getContext('entity');
    assert($entity instanceof FieldableEntityInterface);
    // Check if the any of the values for the field match the configured value.
    $values = $entity->get($this->fieldName)->getValue();
    // TODO: explore the field configuration to learn about the main property.
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

  public function summary(): string {
    return $this->t('Active when the %field is %value.', [
      '%field' => $this->fieldName,
      '%value' => $this->value,
    ]);
  }

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
