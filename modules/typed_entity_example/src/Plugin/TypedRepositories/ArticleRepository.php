<?php

namespace Drupal\typed_entity_example\Plugin\TypedRepositories;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity_example\WrappedEntities\Article;

/**
 * The repository for articles.
 *
 * @TypedRepository(
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
 *       "Drupal\typed_entity_example\Render\Article\Full",
 *     }
 *   ),
 *   description = @Translation("Repository that holds business logic applicable to all articles.")
 * )
 */
final class ArticleRepository extends TypedRepositoryBase implements AccessibleInterface {

  /**
   * The field that contains the data about the article tags.
   */
  const FIELD_TAGS_NAME = 'field_tags';

  /**
   * Finds article by tags.
   *
   * This method is not being used anywhere, this is here only as an example.
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
    return array_filter(
      $this->wrapMultipleById($items),
      static fn(WrappedEntityInterface $wrapped) => $wrapped instanceof Article
    );
  }

  /**
   * Counts the number of published articles.
   *
   * @return int
   *   The number of published articles.
   */
  private function countPublishedEntities(): int {
    try {
      $query = $this->getQuery();
    }
    catch (PluginException $e) {
      return 0;
    }
    return $query
      ->condition('status', TRUE)
      ->count()
      ->execute();
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
  private function findItemsByTags(array $tags): array {
    $query = $this->getQuery();
    // Find all the articles that have at least one of the tags with insensitive
    // case match.
    $field_path = self::FIELD_TAGS_NAME . '.entity.name';
    $orGroup = array_reduce(
      $tags,
      static function (ConditionInterface $orCondition, string $tag) use ($field_path) {
        return $orCondition->condition($field_path, $tag, 'LIKE');
      },
      $query->orConditionGroup()
    );
    return $query
      ->condition($orGroup)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $this->countPublishedEntities() > 8
      ? AccessResult::forbidden('More than eight articles is forbidden.')
      : AccessResult::neutral();
  }

}
