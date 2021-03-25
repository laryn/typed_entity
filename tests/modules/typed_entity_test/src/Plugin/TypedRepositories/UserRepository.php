<?php

namespace Drupal\typed_entity_test\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedRepository(
 *   entity_type_id = "user",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_test\WrappedEntities\User"
 *   )
 * )
 */
final class UserRepository extends TypedRepositoryBase {}
