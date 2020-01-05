<?php

namespace Drupal\typed_entity\TypedRepositories;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\typed_entity\EntityWrappers\EntityWrapperInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use UnexpectedValueException;

class TypedEntityRepositoryBase implements TypedEntityRepositoryInterface {

  protected static $wrapperClass = NULL;

  /**
   * The container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type for this repository.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  private $entityType;

  /**
   * The bundle name.
   *
   * @var string
   */
  private $bundle;

  /**
   * RepositoryCollector constructor.
   *
   * @param  $container
   *   The service container.
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
    $this->entityTypeManager = $container->get('entity_type.manager');
  }

  /**
   * {@inheritdoc}
   */
  public function createFromEntity(EntityInterface $entity): EntityWrapperInterface {
    if (class_exists(static::$wrapperClass)) {
      throw new UnexpectedValueException(
        'Invalid class for the entity wrapper: "' . static::$wrapperClass . '"'
      );
    }
    return call_user_func(
      [static::$wrapperClass, 'create'],
      $this->container,
      $entity
    );
  }

  /**
   * {@inheritdoc}
   */
  public function init(EntityTypeInterface $entity_type, string $bundle) {
    $this->validateArguments($entity_type, $bundle);
    $this->entityType = $entity_type;
    $this->bundle = $bundle;
  }

  private function validateArguments(EntityTypeInterface $entity_type, string $bundle) {
    $bundle_info = $this->container
      ->get('entity_type.bundle.info')
      ->getBundleInfo($entity_type->id());
    if (empty($bundle)) {
      if (!empty($bundle_info)) {
        throw new UnexpectedValueException('Missing bundle for entity type "' . $entity_type->id() . '"');
      }
      return;
    }
    if (empty($bundle_info[$bundle])) {
      $message = 'The bundle "' . $bundle . '" is not valid for entity type "' . $entity_type->id() . '"';
      throw new UnexpectedValueException($message);
    }
  }

}