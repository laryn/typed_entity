<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The wrapped entity for the article content type.
 */
final class Article extends WrappedEntityBase {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  private $messenger;

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
  public static function create(ContainerInterface $container, EntityInterface $entity) {
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
    $message = 'The owner was accessed for article:' . $this->getEntity()->id();
    $this->messenger->addMessage($message);
    return parent::owner();
  }

}
