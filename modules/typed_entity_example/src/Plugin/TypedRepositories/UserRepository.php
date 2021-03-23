<?php

namespace Drupal\typed_entity_example\Plugin\TypedRepositories;

use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedRepository(
 *   entity_type_id = "user",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_example\WrappedEntities\User",
 *   ),
 *   description = @Translation("Repository that holds business logic applicable to all users.")
 * )
 */
final class UserRepository extends TypedRepositoryBase {}
