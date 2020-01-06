<?php


namespace Drupal\typed_entity\WrappedEntityVariants;


use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\typed_entity\InvalidValueException;

interface ContextAwareInterface {

  public function getContext(string $name);
  public function setContext(string $name, $data): void;

  /**
   * Validates the context.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   */
  public function validateContext(): void;

}
