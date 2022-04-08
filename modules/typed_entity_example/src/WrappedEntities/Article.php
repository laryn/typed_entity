<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The wrapped entity for the article content type.
 */
class Article extends WrappedEntityBase implements AccessibleInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private MessengerInterface $messenger;

  /**
   * Article constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity to wrap.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityInterface $entity, MessengerInterface $messenger) {
    parent::__construct($entity);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityInterface $entity): self {
    return new static(
      $entity,
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   *
   * This is only overridden for educational purposes.
   */
  public function owner(): ?WrappedEntityInterface {
    $owner = parent::owner();
    if (!$owner instanceof User) {
      return NULL;
    }
    $message = 'The owner ' . $owner->nickname() . ' was accessed for article: ' . $this->getEntity()->id();
    $this->messenger->addMessage($message);
    return $owner;
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $owner = $this->owner();
    \assert($owner instanceof User);
    $nickname = $owner->nickname();
    return $this->checkInappropriateLanguage($nickname)
      ? AccessResult::forbidden('Nickname of the article\'s author is not appropriate.')
      : AccessResult::neutral();
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
    $forbidden_words = ['synergy', 'disruption'];
    return array_reduce(
      $forbidden_words,
      static fn($found, $forbidden_word) => $found || preg_match('/' . preg_quote($forbidden_word, '/') . '/', $input),
      FALSE
    );
  }

}
