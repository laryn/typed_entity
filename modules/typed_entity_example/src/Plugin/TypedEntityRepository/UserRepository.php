<?php

namespace Drupal\typed_entity_example\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedEntityRepository(
 *   entity_type_id = "user",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_example\WrappedEntities\User",
 *   ),
 *   description = @Translation("Repository that holds business logic applicable to all users.")
 * )
 */
final class UserRepository extends TypedEntityRepositoryBase {}
