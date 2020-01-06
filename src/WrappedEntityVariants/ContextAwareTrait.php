<?php


namespace Drupal\typed_entity\WrappedEntityVariants;


trait ContextAwareTrait {

  protected $contexts = [];

  public function getContext(string $name) {
    return $this->contexts[$name] ?? NULL;
  }

  public function setContext(string $name, $data): void {
    $this->contexts[$name] = $data;
  }

}
