<?php

namespace Drupal\typed_entity_test\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedRepository(
 *   entity_type_id = "node",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_test\WrappedEntities\Node"
 *   )
 * )
 */
final class NodeRepository extends TypedRepositoryBase {}
