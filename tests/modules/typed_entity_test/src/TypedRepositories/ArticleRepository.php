<?php

namespace Drupal\typed_entity_test\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;
use Drupal\typed_entity_test\WrappedEntities\NewsArticle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The repository for articles.
 */
class ArticleRepository extends TypedEntityRepositoryBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(ContainerInterface $container) {
    parent::__construct($container);
    $this->variantConditions = [
      new FieldValueVariantCondition('field_type', 'News', NewsArticle::class),
    ];
  }

}
