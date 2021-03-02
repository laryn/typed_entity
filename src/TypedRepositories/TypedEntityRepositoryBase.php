<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Component\Assertion\Inspector;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\typed_entity\InvalidValueException;
use Drupal\typed_entity\Render\TypedEntityRenderContext;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\Render\TypedEntityRendererInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Drupal\typed_entity\WrappedEntities\WrappedEntityInterface;
use Drupal\typed_entity\WrappedEntityVariants\ContextAwareInterface;
use Drupal\typed_entity\WrappedEntityVariants\VariantConditionInterface;
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
   * Variant conditions.
   *
   * @var \Drupal\typed_entity\WrappedEntityVariants\VariantConditionInterface[]
   */
  protected $variantConditions = [];

  /**
   * The renderers for this repository keyed by ID.
   *
   * @var \Drupal\typed_entity\Render\TypedEntityRendererInterface[]
   */
  protected $renderers = [];

  /**
   * The fallback renderer used when no renderer is appropriate.
   *
   * @var \Drupal\typed_entity\Render\TypedEntityRendererInterface
   */
  protected $fallbackRenderer;

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
  public function wrap(EntityInterface $entity): ?WrappedEntityInterface {
    // Validate that this entity can be wrapped.
    $can_be_wrapped = $this->entityType->id() === $entity->getEntityTypeId()
      && $this->bundle === $entity->bundle();
    if (!$can_be_wrapped) {
      return NULL;
    }
    $class = $this->negotiateVariant($entity);
    return call_user_func(
      [$class, 'create'],
      $this->container,
      $entity
    );
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.StaticAccess)
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
   * Negotiates possible variants to the default based on entity values.
   *
   * Override this in the repositories that need variance.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return string
   *   The negotiated variant.
   */
  protected function negotiateVariant(EntityInterface $entity): string {
    // Match the first variant condition found.
    foreach ($this->variantConditions as $variant_condition) {
      if ($variant_condition instanceof ContextAwareInterface) {
        $variant_condition->setContext('entity', $entity);
      }
      assert($variant_condition instanceof VariantConditionInterface);
      try {
        $valid = $variant_condition->evaluate();
      }
      catch (InvalidValueException $e) {
        $valid = FALSE;
      }
      if ($valid) {
        // Only use it if the variant is also a wrapperClass.
        $variant = $variant_condition->variant();
        if (class_exists($variant) && is_subclass_of($variant, $this->wrapperClass)) {
          // Return early to avoid evaluating more conditions.
          return $variant;
        }
      }
    }
    // If none matches use the wrapper class.
    return $this->wrapperClass;
  }

  /**
   * {@inheritdoc}
   */
  public function init(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class, string $fallback_renderer_id = ''): void {
    $fallback_renderer_id = empty($fallback_renderer_id)
      ? TypedEntityRendererBase::class
      : $fallback_renderer_id;
    $this->validateArguments($entity_type, $bundle, $wrapper_class, $fallback_renderer_id);
    $this->entityType = $entity_type;
    $this->bundle = $entity_type->getKey('bundle')
      ? $bundle
      : $entity_type->id();
    $this->fallbackRenderer = $this->container->get($fallback_renderer_id);
    $this->wrapperClass = $wrapper_class;
  }

  /**
   * {@inheritdoc}
   *
   * @SuppressWarnings(PHPMD.ShortMethodName)
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
   * {@inheritdoc}
   */
  public function rendererFactory(TypedEntityRenderContext $context): array {
    $valid_renderers = array_filter(
      $this->renderers,
      static function (TypedEntityRendererInterface $renderer) use ($context) {
        return $renderer::applies($context);
      }
    );
    return empty($valid_renderers) ? [$this->fallbackRenderer()] : $valid_renderers;
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
   * @param string $fallback_renderer_id
   *   The service ID for the fallback renderer.
   *
   * @throw \UnexpectedValueException;
   */
  private function validateArguments(EntityTypeInterface $entity_type, string $bundle, string $wrapper_class, string $fallback_renderer_id): void {
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
    if (!is_a($wrapper_class, WrappedEntityBase::class, TRUE)) {
      $message = 'The wrapper class "' . $wrapper_class . '" must extend "' . WrappedEntityBase::class . '" for backwards compatibility safety.';
      throw new UnexpectedValueException($message);
    }
    // Ensure there is a service for the fallback renderer ID.
    if (!$this->container->has($fallback_renderer_id)) {
      $message = 'Non-existing service "' . $fallback_renderer_id . '" provided as a fallback renderer.';
      throw new UnexpectedValueException($message);
    }
    // Ensure the fallback renderer is appropriate.
    $fallback_renderer = $this->container->get($fallback_renderer_id);
    if (!is_a($fallback_renderer, TypedEntityRendererBase::class)) {
      $message = 'The fallback renderer "' . get_class($fallback_renderer) . '" must extend "' . TypedEntityRendererBase::class . '" for backwards compatibility safety.';
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
  public function addRenderer(TypedEntityRendererInterface $renderer): void {
    $this->renderers[] = $renderer;
  }

  /**
   * The fallback renderer.
   *
   * @return \Drupal\typed_entity\Render\TypedEntityRendererInterface
   *   The renderer.
   */
  protected function fallbackRenderer(): TypedEntityRendererInterface {
    return $this->fallbackRenderer;
  }

}
