<?php

namespace Drupal\typed_entity\Annotation;

use Drupal\Core\Annotation\Translation;
use Drupal\Component\Annotation\Plugin;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

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
  public string $entity_type_id;

  /**
   * The bundle.
   *
   * @var string
   */
  public string $bundle;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $description;

  /**
   * The available wrappers.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariants
   *   The wrapper with the variants.
   */
  public ClassWithVariants $wrappers;

  /**
   * The available renderers.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariants
   *   The wrapper with the variants.
   */
  public ClassWithVariants $renderers;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    return $this->definition['id']
      ?? TypedRepositoryBase::generatePluginId(
        $this->definition['entity_type_id'] ?? '',
        $this->definition['bundle'] ?? '',
      );
  }

}
