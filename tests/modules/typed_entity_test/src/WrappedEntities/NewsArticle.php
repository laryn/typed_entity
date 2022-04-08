<?php

namespace Drupal\typed_entity_test\WrappedEntities;

use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;

/**
 * The wrapped entity for the article content type.
 */
class NewsArticle extends Article {

  /**
   * Get the byline for the article.
   */
  public function getByline(): string {
    return 'By John Doe';
  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    $condition = new FieldValueVariantCondition(
      'field_node_type',
      'News',
      $context
    );
    try {
      return $condition->evaluate();
    }
    catch (InvalidValueException $exception) {
      return FALSE;
    }
  }

}
