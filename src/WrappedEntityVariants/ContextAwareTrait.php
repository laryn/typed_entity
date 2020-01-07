<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

/**
 * Trait for classes implementing ContextAwareInterface.
 */
trait ContextAwareTrait {

  /**
   * The in-memory key/value store.
   *
   * @var array
   */
  protected $contexts = [];

  /**
   * {@inheritdoc}
   */
  public function getContext(string $name) {
    return $this->contexts[$name] ?? NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function setContext(string $name, $data): void {
    $this->contexts[$name] = $data;
  }

}
