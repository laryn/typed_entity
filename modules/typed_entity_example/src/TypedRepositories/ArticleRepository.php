<?php

namespace Drupal\typed_entity_example\TypedRepositories;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\ConditionInterface;
use Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryBase;
use Drupal\typed_entity\WrappedEntityVariants\FieldValueVariantCondition;
use Drupal\typed_entity_example\WrappedEntities\BakingArticle;

/**
 * The repository for articles.
 */
final class ArticleRepository extends TypedEntityRepositoryBase {

  /**
   * The field that contains the data about the article tags.
   */
  const FIELD_TAGS_NAME = 'field_tags';

  /**
   * {@inheritdoc}
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class, string $fallback_renderer_id = ''): void {
    parent::init($entity_type, $bundle, $wrapper_class, $fallback_renderer_id);
    $field_map = $this->container->get('entity_field.manager')->getFieldMap();
    $has_field = $field_map[$entity_type->id()][static::FIELD_TAGS_NAME]['bundles'][$bundle] ?? NULL;
    if ($has_field) {
      $this->variantConditions = [
        new FieldValueVariantCondition(static::FIELD_TAGS_NAME, 24, BakingArticle::class),
      ];
    }
  }

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
