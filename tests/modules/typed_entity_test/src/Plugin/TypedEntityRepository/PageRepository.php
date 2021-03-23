<?php

namespace Drupal\typed_entity_test\Plugin\TypedEntityRepository;

use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedEntityRepository(
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
final class PageRepository extends TypedEntityRepositoryBase {}
