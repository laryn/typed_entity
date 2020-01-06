<?php

namespace Drupal\typed_entity\WrappedEntityVariants;

interface VariantConditionInterface {

  public function isNegated(): bool;
  public function evaluate(): bool;
  public function summary(): string;
  public function variant(): string;

}
