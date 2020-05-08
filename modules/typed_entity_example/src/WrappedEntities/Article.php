<?php

namespace Drupal\typed_entity_example\WrappedEntities;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\typed_entity\RepositoryManager;
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
   * @param \Drupal\typed_entity\RepositoryManager $repository_manager
   *   The repository manager.
   * @param \Drupal\Core\Entity\EntityViewBuilderInterface $entity_view_builder
   *   The view builder.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(EntityInterface $entity, RepositoryManager $repository_manager, EntityViewBuilderInterface $entity_view_builder, MessengerInterface $messenger) {
    parent::__construct($entity, $repository_manager, $entity_view_builder);
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, EntityInterface $entity) {
    $entity_view_builder = $container->get('entity_type.manager')
      ->getViewBuilder($entity->getEntityTypeId());
    $repository_manager = $container->get(RepositoryManager::class);
    return new static(
      $entity,
      $repository_manager,
      $entity_view_builder,
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
