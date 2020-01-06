<?php

namespace Drupal\typed_entity_example\TypedRepositories;

use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;

/**
 * The repository for articles.
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
   * @throws \Drupal\typed_entity\InvalidValueException
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
   * @throws \Drupal\typed_entity\InvalidValueException
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
