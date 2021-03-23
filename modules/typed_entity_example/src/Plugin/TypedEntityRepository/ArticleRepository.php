<?php

namespace Drupal\typed_entity_example\Plugin\TypedEntityRepository;

use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;

/**
 * The repository for articles.
 *
 * @TypedEntityRepository(
 *   entity_type_id = "node",
 *   bundle = "article",
 *   wrappers = @ClassWithVariants(
 *     fallback = "Drupal\typed_entity_example\WrappedEntities\Article",
 *     variants = {
 *       "Drupal\typed_entity_example\WrappedEntities\BakingArticle",
 *     }
 *   ),
 *   renderers = @ClassWithVariants(
 *     variants = {
 *       "Drupal\typed_entity_example\Render\Summary",
 *     }
 *   ),
 *   description = @Translation("Repository that holds business logic applicable to all articles.")
 * )
 */
final class ArticleRepository extends TypedEntityRepositoryBase {

  /**
   * The field that contains the data about the article tags.
   */
  const FIELD_TAGS_NAME = 'field_tags';

  /**
   * Finds article by tags.
   *
   * @param string[] $tags
   *   The tags to search for.
   *
   * @return \Drupal\typed_entity_example\WrappedEntities\Article[]
   *   The wrapped entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function findByTags(array $tags): array {
    $items = $this->findItemsByTags($tags);
    return $this->wrapMultipleById($items);
  }

  /**
   * Find the entity IDs for the articles tagged with any of the provided tags.
   *
   * @param array $tags
   *   The list of tags.
   *
   * @return array
   *   The result of the execution of the query.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function findItemsByTags(array $tags) {
    $query = $this->getQuery();
    // Find all the articles that have at least one of the tags with insensitive
    // case match.
    $field_path = static::FIELD_TAGS_NAME . '.entity.name';
    $orGroup = array_reduce(
      $tags,
      function (ConditionInterface $orCondition, string $tag) use ($field_path) {
        return $orCondition->condition($field_path, $tag, 'LIKE');
      },
      $query->orConditionGroup()
    );
    return $query
      ->condition($orGroup)
      ->execute();
  }

}
