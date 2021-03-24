<?php

namespace Drupal\typed_entity_ui\Controller;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;
use Drupal\typed_entity\WrappedEntities\WrappedEntityBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller to print the typed entity info for a given type.
 */
class ExploreDetails extends ControllerBase {

  /**
   * The entity type manager to manage entity type plugin definitions.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle service to discover & retrieve entity type bundles.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $bundleInfo;

  /**
   * The repository manager.
   *
   * @var \Drupal\typed_entity\RepositoryManager
   */
  protected $repositoryManager;

  /**
   * Constructs a new EntityBundlePicker form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundle_info
   *   The entity type bundle info service for discovering entity type bundles.
   * @param \Drupal\typed_entity\RepositoryManager $repository_manager
   *   The repository manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $bundle_info, RepositoryManager $repository_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->bundleInfo = $bundle_info;
    $this->repositoryManager = $repository_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get(RepositoryManager::class)
    );
  }

  /**
   * Set the page title.
   *
   * @param string $typed_entity_id
   *   The typed entity ID.
   *
   * @return \Drupal\Component\Render\MarkupInterface
   *   The title.
   */
  public function title(string $typed_entity_id): MarkupInterface {
    [$entity_type_label, $bundle_label] = $this->getLabels($typed_entity_id);
    if ($bundle_label) {
      return $this->t(
        'Explore typed entity: %bundle (%type)',
        ['%type' => $entity_type_label, '%bundle' => $bundle_label]
      );
    }
    return $this->t('Explore typed entity: %type', ['%type' => $entity_type_label]);
  }

  /**
   * Handles the request.
   *
   * @param string $typed_entity_id
   *   The typed entity ID.
   *
   * @return array
   *   The render array.
   */
  public function __invoke(string $typed_entity_id): array {
    $bundle = '';
    $entity_type_id = $typed_entity_id;
    if (strpos($typed_entity_id, '.') !== FALSE) {
      [$entity_type_id, $bundle] = explode('.', $typed_entity_id, 2);
    }
    $repository = $this->repositoryManager->repository($entity_type_id, $bundle ?? '');
    if (!$repository instanceof TypedRepositoryInterface) {
      return $this->getNotFoundOutput($typed_entity_id);
    }
    assert($repository instanceof PluginInspectionInterface);
    $definition = $repository->getPluginDefinition();
    $wrappers = $definition['wrappers'] ?? NULL;
    $renderers = $definition['renderers'] ?? NULL;
    $description = $definition['description'] ?? NULL;

    $reflection_repo = new \ReflectionClass($repository);
    return [
      [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Typed Repository'),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $description,
      ],
      [
        '#theme' => 'php_class_info',
        '#reflection' => $reflection_repo,
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Entity wrappers'),
      ],
      [
        '#theme' => 'class_with_variants',
        '#object' => $wrappers,
        '#base_class' => WrappedEntityBase::class,
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => $this->t('Entity renderers'),
      ],
      [
        '#theme' => 'class_with_variants',
        '#object' => $renderers,
        '#base_class' => TypedEntityRendererBase::class,
      ],
    ];
  }

  /**
   * Extract the entity type and bundle labels from the typed entity ID.
   *
   * @param string $typed_entity_id
   *   The ID.
   *
   * @return array
   *   The labels.
   */
  protected function getLabels(string $typed_entity_id): array {
    $bundle = '';
    $entity_type_id = $typed_entity_id;
    if (strpos($typed_entity_id, '.') !== FALSE) {
      [$entity_type_id, $bundle] = explode('.', $typed_entity_id, 2);
    }
    try {
      $entity_type_label = $this->entityTypeManager
        ->getDefinition($entity_type_id)
        ->getLabel();
    }
    catch (PluginNotFoundException $exception) {
      return [NULL, NULL];
    }
    if ($bundle) {
      $bundle_label = $this->bundleInfo->getBundleInfo($entity_type_id)[$bundle]['label'] ?? '';
      return [$entity_type_label, $bundle_label];
    }
    return [$entity_type_label, NULL];
  }

  /**
   * Output when there is no typed entity repository associated.
   *
   * @param string $typed_entity_id
   *   The typed entity ID.
   *
   * @return array
   *   The render array.
   */
  protected function getNotFoundOutput(string $typed_entity_id): array {
    [$entity_type_label, $bundle_label] = $this->getLabels($typed_entity_id);
    return [
      [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => $this->t('Not found'),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('Unable to find a repository for %type (%bundle).', [
          '%type' => $entity_type_label,
          '%bundle' => $bundle_label ?? $entity_type_label,
        ]),
      ],
      [
        '#type' => 'html_tag',
        '#tag' => 'p',
        '#value' => $this->t('See the <a href=:docs>documentation</a> to learn how to associate a typed entity repository to type of entity.', [
          ':docs' => 'https://www.drupal.org/typed_entity',
        ]),
      ],
    ];
  }

}
