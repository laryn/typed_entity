<?php

namespace Drupal\typed_entity_test\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedRepository(
 *   entity_type_id = "node",
 *   bundle = "article",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_test\WrappedEntities\Article",
 *     variants = {
 *       "Drupal\typed_entity_test\WrappedEntities\NewsArticle",
 *     }
 *   ),
 *   renderers = @ClassWithVariants(
 *     variants = {
 *       "Drupal\typed_entity_test\Render\Article\Teaser",
 *       "Drupal\typed_entity_test\Render\Article\ConditionalRenderer",
 *     }
 *   )
 * )
 */
final class ArticleRepository extends TypedRepositoryBase {}
