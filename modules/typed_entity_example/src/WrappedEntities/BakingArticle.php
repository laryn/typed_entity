<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;
use Drupal\typed_entity_example\Plugin\TypedEntityRepository\ArticleRepository;

/**
 * The wrapped entity for the article content type tagged with Baking.
 */
final class BakingArticle extends Article {

  /**
   * An example method that is specific for articles about baking.
   *
   * This is not useful at all, but used only as an example.
   *
   * @return string
   *   Either yeast or baking soda.
   */
  public function yeastOrBakingSoda(): string {
    return mt_rand(0, 1) ? 'yeast' : 'baking soda';
  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    $condition = new FieldValueVariantCondition(
      ArticleRepository::FIELD_TAGS_NAME,
      24,
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
