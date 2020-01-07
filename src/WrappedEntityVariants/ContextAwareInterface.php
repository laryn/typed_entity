<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

/**
 * Implementors will have support for context.
 */
interface ContextAwareInterface {

  /**
   * Gets the context value filed under the provided name.
   *
   * @param string $name
   *   The context name.
   *
   * @return mixed
   *   The context value.
   */
  public function getContext(string $name);

  /**
   * Sets a context value under a specific name.
   *
   * @param string $name
   *   The context name.
   * @param mixed $data
   *   The value to store.
   */
  public function setContext(string $name, $data): void;

  /**
   * Validates the context.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   */
  public function validateContext(): void;

}
