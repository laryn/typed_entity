<?php

namespace Drupal\typed_entity_ui\Form;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryBase;
use Drupal\typed_entity\TypedRepositoryPluginManager;
use Drupal\typed_entity_ui\Form\RepositoryTable\BuildRepositoryTableService;
use Drupal\typed_entity_ui\Form\RepositoryTable\RepositoryTableRequest;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form to select an entity type and a bundle to explore the typed entity info.
 *
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
class ExploreForm extends FormBase {

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
   * The plugin manager.
   *
   * @var \Drupal\typed_entity\RepositoryManager
   */
  protected $repositoryManager;

  /**
   * Constructs a new Explore form form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityTypeBundleInfoInterface $bundleInfo
   *   The entity type bundle info service for discovering entity type bundles.
   * @param \Drupal\typed_entity\RepositoryManager $repository_manager
   *   The plugin manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityTypeBundleInfoInterface $bundleInfo, RepositoryManager $repository_manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->bundleInfo = $bundleInfo;
    $this->repositoryManager = $repository_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info'),
      $container->get(RepositoryManager::class)
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'typed_entity_ui_explore';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $content_entity_types = array_filter($this->entityTypeManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type instanceof ContentEntityTypeInterface;
    });
    $entity_types = array_reduce($content_entity_types, function ($carry, EntityTypeInterface $entity_type) {
      $carry[$entity_type->id()] = $entity_type->getLabel();
      return $carry;
    }, []);

    $form['entity_type_id'] = [
      '#title' => $this->t('Entity Type'),
      '#type' => 'select',
      '#options' => $entity_types,
      '#empty_option' => $this->t('- Select -'),
      '#required' => TRUE,
      '#ajax' => [
        'callback' => '::bundleCallback',
        'wrapper' => 'bundle-wrapper',
      ],
    ];

    // Disable caching on this form.
    $form_state->setCached(FALSE);

    $form['actions'] = [
      '#type' => 'actions',
    ];

    // Add a submit button that handles the submission of the form.
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Explore'),
    ];

    $form['bundle_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'bundle-wrapper'],
    ];
    $entity_type_id = $form_state->getValue('entity_type_id');
    if ($entity_type_id) {
      try {
        $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      }
      catch (PluginNotFoundException $e) {
        return $form;
      }
      $has_bundles = (bool) $entity_type->getBundleEntityType();
      if ($has_bundles) {
        $bundles = [];
        $bundle_info = $this->bundleInfo->getBundleInfo($entity_type_id);
        foreach ($bundle_info as $bundle_id => $info) {
          $bundles[$bundle_id] = $info['translatable']
            ? $this->t($info['label'])
            : $info['label'];
        }
        $form['bundle_wrapper']['bundle'] = [
          '#type' => 'select',
          '#empty_option' => $this->t('- Select -'),
          '#title' => $this->t('Bundle'),
          '#options' => $bundles,
        ];
      }
    }
    $table_builder = new BuildRepositoryTableService();
    $as_request = new RepositoryTableRequest($this->repositoryManager);
    $title = $this->t('Filter typed repositories by plugin ID.');
    $form['table'] = [
      '#type' => 'details',
      '#title' => $this->t('Available Repositories'),
      '#description' => $this->t('All the available repositories registered in the application. If you just created a new typed repository but it is not listed here try clearing caches.'),
      '#open' => TRUE,
      '#weight' => 101,
      'search' => [
        '#type' => 'search',
        '#title' => $this->t('Filter'),
        '#placeholder' => $title,
        '#attributes' => [
          'class' => ['typed-repositories-filter-text'],
          'data-table' => 'table#edit-data',
          'autocomplete' => 'off',
          'title' => $title,
        ],
        '#attached' => [
          'library' => ['typed_entity_ui/admin'],
        ],
      ],
      'data' => $table_builder->execute($as_request)->getBuild(),
    ];
    return $form;
  }

  /**
   * Implements callback for Ajax event on entity type selection.
   *
   * @param array $form
   *   From render array.
   *
   * @return array
   *   Color selection section of the form.
   *
   * @SuppressWarnings(PHPMD.)
   */
  public function bundleCallback(array $form): array {
    return $form['bundle_wrapper'];
  }

  /**
   * Implements a form submit handler.
   *
   * @param array $form
   *   The render array of the currently built form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Object describing the current state of the form.
   *
   * @SuppressWarnings(PHPMD.UnusedFormalParameter)
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $typed_entity_id = implode(
      TypedRepositoryBase::ID_PARTS_SEPARATOR,
      array_filter([
        $form_state->getValue('entity_type_id'),
        $form_state->getValue('bundle'),
      ]));
    $form_state->setRedirect(
      'typed_entity_ui.details',
      ['typed_entity_id' => $typed_entity_id]
    );
  }

}
