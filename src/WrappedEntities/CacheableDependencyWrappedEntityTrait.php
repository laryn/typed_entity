<?php

namespace Drupal\typed_entity\WrappedEntities;

/**
 * Trait to add support for cacheable dependencies.
 */
trait CacheableDependencyWrappedEntityTrait {

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->getEntity()->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getEntity()->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->getEntity()->getCacheMaxAge();
  }

}
