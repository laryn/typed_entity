<?php

namespace Drupal\typed_entity_test\TypedRepositories;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;

/**
 * The repository for articles.
 */
class ArticleRepository extends TypedEntityRepositoryBase {

  /**
   * {@inheritdoc}
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class, string $fallback_renderer_id = ''): void {
    parent::init($entity_type, $bundle, $wrapper_class, $fallback_renderer_id);
    $this->variantConditions = [
      new FieldValueVariantCondition('field_node_type', 'News', NewsArticle::class),
    ];
  }

}
