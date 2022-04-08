<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity_example\Plugin\TypedRepositories\ArticleRepository;

/**
 * The wrapped entity for the article content type tagged with Baking.
 */
final class BakingArticle extends Article {

  /**
   * An example method that is specific for articles about baking.
   *
   * This is not useful at all, but used only as an example.
   *
   * @return string
   *   Either yeast or baking soda.
   */
  public function yeastOrBakingSoda(): string {
    return random_int(0, 1) ? 'yeast' : 'baking soda';
  }

  /**
   * Fake service that checks for inappropriate words.
   *
   * @pararm string $input
   *   The string to check.
   *
   * @return bool
   *   TRUE if it contains inappropriate language.
   */
  protected function checkInappropriateLanguage(string $input): bool {
    $forbidden_words = ['flat', 'unfluffy'];
    return array_reduce($forbidden_words, static function ($found, $forbidden_word) use ($input) {
      return $found || preg_match('/' . preg_quote($forbidden_word, '/') . '/', $input);
    }, FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    $entity = $context->offsetGet('entity');
    if ($entity instanceof EntityInterface) {
      return FALSE;
    }
    return in_array([
      'Baking',
      'Baked',
    ], $entity->{ArticleRepository::FIELD_TAGS_NAME}->entity->getName(), TRUE);
  }

}
