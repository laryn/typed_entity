<?php

namespace Drupal\typed_entity_test\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedRepository(
 *   entity_type_id = "node",
 *   bundle = "page",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_test\WrappedEntities\Page"
 *   ),
 *   renderers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_test\Render\Page\Base"
 *   )
 * )
 */
final class PageRepository extends TypedRepositoryBase {}
