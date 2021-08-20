<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\typed_entity\Annotation\ClassWithVariantsInterface;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\Render\TypedEntityRendererInterface;
use Drupal\typed_entity\TypedEntityContext;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class all repositories should extend from.
 */
class TypedRepositoryBase extends PluginBase implements TypedRepositoryInterface, ContainerFactoryPluginInterface {

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
   * The renderers for this repository.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariantsInterface
   */
  protected $renderers = NULL;

  /**
   * The wrappers for this repository.
   *
   * @var \Drupal\typed_entity\Annotation\ClassWithVariantsInterface
   */
  protected $wrappers = NULL;

  /**
   * TypedEntityRepositoryBase constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param array $plugin_definition
   *   Plugin definition.
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   *
   * @throws \UnexpectedValueException
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, ContainerInterface $container) {
    $this->container = $container;
    $this->validateAnnotation($plugin_definition);
    $this->entityTypeManager = $container->get('entity_type.manager');
    $this->entityType = $this->entityTypeManager
      ->getDefinition($plugin_definition['entity_type_id']);
    $this->bundle = $plugin_definition['bundle'] ?? NULL;
    $this->wrappers = $plugin_definition['wrappers'] ?? NULL;
    $this->renderers = $plugin_definition['renderers'] ?? NULL;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container);
  }

  /**
   * {@inheritdoc}
   */
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface {
    // Validate that this entity can be wrapped.
    if ($this->entityType->id() !== $entity->getEntityTypeId()) {
      return NULL;
    }
    // We only want to enforce matching the bundle if the bundle was explicitly
    // set in the typed repository, and the entity type supports bundles.
    $bundle_is_supported = !empty($entity->getEntityType()->getKey('bundle'));
    if ($bundle_is_supported && $this->bundle && $this->bundle !== $entity->bundle()) {
      return NULL;
    }
    return $this->wrapperFactory(new TypedEntityContext(['entity' => $entity]));
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
   */
  public function wrapMultiple(array $entities): array {
    // Wrap all the entities.
    return array_map([$this, 'wrap'], $entities);
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.ShortMethodName)
   */
  public function id(): string {
    return static::generatePluginId($this->entityType->id(), $this->bundle ?: '');
  }

  /**
   * {@inheritdoc}
   */
  public static function generatePluginId(string $entity_type_id, string $bundle = ''): string {
    return implode(
      static::ID_PARTS_SEPARATOR,
      array_filter([$entity_type_id, $bundle])
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
   * {@inheritdoc}
   */
  public function wrapperFactory(TypedEntityContext $context): ?WrappedEntityInterface {
    $entity = $context->offsetGet('entity');
    if (!$entity instanceof EntityInterface) {
      throw new \UnexpectedValueException('Missing entity in context.');
    }
    $wrappers = $this->getPluginDefinition()['wrappers'] ?? NULL;
    if (!$wrappers) {
      return NULL;
    }
    assert($wrappers instanceof ClassWithVariantsInterface);
    $class = $wrappers->negotiateVariant($context, WrappedEntityBase::class);
    return $class
      ? call_user_func_array([$class, 'create'], [$this->container, $entity])
      : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function rendererFactory(TypedEntityContext $context): ?TypedEntityRendererInterface {
    $renderers = $this->getPluginDefinition()['renderers'] ?? NULL;
    if (!$renderers) {
      return NULL;
    }
    assert($renderers instanceof ClassWithVariantsInterface);
    $class = $renderers->negotiateVariant($context, TypedEntityRendererBase::class) ?? TypedEntityRendererBase::class;
    return call_user_func_array([$class, 'create'], [$this->container]);
  }

  /**
   * Validates the repository annotation.
   *
   * @param array $plugin_definition
   *   The plugin definition.
   *
   * @throws \UnexpectedValueException;
   */
  private function validateAnnotation(array $plugin_definition): void {
    $entity_type_id = $plugin_definition['entity_type_id'] ?? NULL;
    try {
      $entity_type = $this->container->get('entity_type.manager')
        ->getDefinition($entity_type_id);
    }
    catch (PluginException $exception) {
      throw new \UnexpectedValueException('Unable to find the entity type "' . $entity_type_id . '".');
    }
    $bundle_info = $this->container
      ->get('entity_type.bundle.info')
      ->getBundleInfo($entity_type->id());
    // When the entity type supports bundles, the bundle parameter is mandatory.
    $bundle = $plugin_definition['bundle'] ?? NULL;
    // Unless the entity is bundle-less the bundle should be valid for the given
    // entity type.
    if ($bundle && empty($bundle_info[$bundle])) {
      $message = 'The bundle "' . $bundle . '" is not valid for entity type "' . $entity_type->id() . '"';
      throw new \UnexpectedValueException($message);
    }
    $wrappers = $plugin_definition['wrappers'] ?? NULL;
    if ($wrappers instanceof ClassWithVariantsInterface) {
      // Ensure the wrapper class exists.
      if (!$wrappers->getFallback(WrappedEntityBase::class)) {
        throw new \UnexpectedValueException('The wrapper fallback does not exist.');
      }
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
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function wrapMultipleById(array $items): array {
    // Load all the entities that we found.
    $entities = $this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->loadMultiple(array_values($items));
    // Then wraps them all.
    return $this->wrapMultiple($entities);
  }

  /**
   * Wraps all the entities for the repository.
   *
   * CAUTION: this method can have a performance impact depending on the number
   * of entities to be loaded and wrapped.
   *
   * @param string $operation
   *   The entity operation to use this for. Defaults to 'view'.
   *
   * @return \Drupal\typed_entity\WrappedEntities\WrappedEntityInterface[]
   *   The wrapped entities.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function wrapAll($operation = 'view'): array {
    $bundle_key = $this->entityType->getKey('bundle');
    $entities = $this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->loadByProperties([$bundle_key => $this->bundle]);
    $check_access = static function (EntityInterface $entity) use ($operation) {
      return $entity instanceof AccessibleInterface
        ? $entity->access($operation)
        : TRUE;
    };
    $accessible_entities = array_filter($entities, $check_access);
    return $this->wrapMultiple($accessible_entities);
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType(): EntityTypeInterface {
    return $this->entityType;
  }

  /**
   * {@inheritdoc}
   */
  public function getBundle(): ?string {
    return $this->bundle;
  }

  /**
   * {@inheritdoc}
   */
  public function createEntity(array $values = []): WrappedEntityInterface {
    // Autoset the bundle key, if the typed repository has a bundle and the
    // entity type supports bundles.
    $bundle_key = $this->entityType->getKey('bundle');
    if ($this->bundle && $bundle_key && empty($values[$bundle_key])) {
      $values[$bundle_key] = $this->bundle;
    }

    return $this->wrap($this->entityTypeManager
      ->getStorage($this->entityType->id())
      ->create($values));
  }

}
