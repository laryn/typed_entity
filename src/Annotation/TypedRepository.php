<?php

namespace Drupal\typed_entity\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;

/**
 * Defines typed_entity_repository annotation object.
 *
 * @Annotation
 */
class TypedRepository extends Plugin {

  /**
   * The entity type ID.
   *
   * @var string
   */
  public $entity_type_id;

  /**
   * The bundle.
   *
   * @var string
   */
  public $bundle;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The available wrappers.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariants
   *   The wrapper with the variants.
   */
  public $wrappers;

  /**
   * The available renderers.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariants
   *   The wrapper with the variants.
   */
  public $renderers;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return implode(
      TypedRepositoryInterface::SEPARATOR,
      array_filter([
        $this->definition['entity_type_id'] ?? NULL,
        $this->definition['bundle'] ?? NULL,
      ])
    );
  }

}
