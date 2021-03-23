<?php

namespace Drupal\typed_entity;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for typed_entity_repository plugins.
 */
abstract class TypedEntityRepositoryPluginBase extends PluginBase implements TypedEntityRepositoryInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
