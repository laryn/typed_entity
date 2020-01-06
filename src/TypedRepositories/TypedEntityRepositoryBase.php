<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;

/**
 * Base class all repositories should extend from.
 */
class TypedEntityRepositoryBase implements TypedEntityRepositoryInterface {

  /**
   * The separator between the entity type ID and the bundle name.
   *
   * @var string
   */
  const SEPARATOR = ':';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The service container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The entity type for this repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityType;

  /**
   * The bundle name.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The wrapper class.
   *
   * @var string
   */
  protected $wrapperClass;

  /**
   * RepositoryCollector constructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->entityTypeManager = $container->get('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): WrappedEntityInterface {
    // Validate that this entity can be wrapped.
    $can_be_wrapped = $this->entityType->id() === $entity->getEntityTypeId();
    if ($this->bundle) {
      $can_be_wrapped = $this->bundle === $entity->bundle();
    }
    if (!$can_be_wrapped) {
      throw new InvalidValueException('Unable to wrap entity with this repository.');
    }
    return call_user_func(
      [$this->wrapperClass, 'create'],
      $this->container,
      $entity
    );
  }

  /**
   * {@inheritdoc}
   */
  public function wrapMultiple(array $entities): array {
    // Wrap all the entities.
    $wrapped = array_map([$this, 'wrap'], $entities);

    // We do this because PHP 7 does not support type generics. In a distant
    // future this will be unnecessary as the return type hint will be something
    // like Array<Article>.
    assert(Inspector::assertAll(function ($wrapped_entity) {
      return $wrapped_entity instanceof $this->wrapperClass;
    }, $wrapped));
    return $wrapped;
  }

  /**
   * {@inheritdoc}
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class): void {
    $this->validateArguments($entity_type, $bundle, $wrapper_class);
    $this->entityType = $entity_type;
    $this->bundle = $entity_type->getKey('bundle')
      ? $bundle
      : $entity_type->id();
    $this->wrapperClass = $wrapper_class;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return implode(
      static::SEPARATOR,
      array_filter([$this->entityType->id(), $this->bundle])
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuery(): QueryInterface {
    $bundle_key = $this->entityType->getKey('bundle');
    $query = $this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->getQuery();
    if (!$this->bundle || !$bundle_key) {
      return $query;
    }
    return $query->condition($bundle_key, $this->bundle);
  }

  /**
   * Validates the repository initialization arguments.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type object for the repository.
   * @param string $bundle
   *   The bundle name.
   * @param string $wrapper_class
   *   The wrapper class for entities.
   */
  private function validateArguments(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class): void {
    $bundle_info = $this->container
      ->get('entity_type.bundle.info')
      ->getBundleInfo($entity_type->id());
    // When the entity type supports bundles, the bundle parameter is mandatory.
    if (empty($bundle)) {
      if ($entity_type->getKey('bundle')) {
        throw new UnexpectedValueException('Missing bundle for entity type "' . $entity_type->id() . '"');
      }
      $bundle = $entity_type->id();
    }
    // Unless the entity is bundle-less the bundle should be valid for the given
    // entity type.
    if (empty($bundle_info[$bundle])) {
      $message = 'The bundle "' . $bundle . '" is not valid for entity type "' . $entity_type->id() . '"';
      throw new UnexpectedValueException($message);
    }
    // Ensure the wrapper class exists.
    if (!class_exists($wrapper_class)) {
      $message = 'The wrapper class "' . $wrapper_class . '" could not be found.';
      throw new UnexpectedValueException($message);
    }
    // Ensure the wrapper class implements the expected interface.
    if (is_a($wrapper_class, WrappedEntityInterface::class)) {
      $message = 'The wrapper class "' . $wrapper_class . '" must implement "' . WrappedEntityInterface::class . '".';
      throw new UnexpectedValueException($message);
    }
  }

  /**
   * Wraps multiple entities by entity ID.
   *
   * Note that even when the entities are all of the same type there is no
   * guarantee that they are all of the same bundle. That means that different
   * wrapped entity classes may be returned.
   *
   * @param array $items
   *   The array containing the IDs of the entities to wrap.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped entities.
   *
   * @throws \Drupal\typed_entity\InvalidValueException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function wrapMultipleById(array $items): array {
    // Load all the entities that we found.
    $entities = $this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->loadMultiple(array_values($items));
    // Then wraps them all.
    return $this->wrapMultiple($entities);
  }

}
