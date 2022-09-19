<?php

namespace Drupal\typed_entity_ui\Form\RepositoryTable;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface;

/**
 * Builds the render array for a table with all typed entity repositories.
 */
class BuildRepositoryTableService {

  use StringTranslationTrait;

  /**
   * Application service executor.
   *
   * @param \Drupal\typed_entity_ui\Form\RepositoryTable\RepositoryTableRequest $request
   *   The request object.
   *
   * @return \Drupal\typed_entity_ui\Form\RepositoryTable\RepositoryTableResponse
   *   The response object.
   */
  public function execute(RepositoryTableRequest $request): RepositoryTableResponse {
    return new RepositoryTableResponse([
      '#type' => 'table',
      '#header' => $this->buildHeader(),
      '#title' => $this->t('Available Typed Repositories'),
      '#rows' => $this->buildRows($request->getAllTypedRepositories()),
      '#empty' => $this->t(
        'There are no typed repositories yet. Check the <a href="@link">documentation</a> to learn how to create one.',
        ['@link' => 'https://www.drupal.org/project/typed_entity']
      ),
    ]);
  }

  /**
   * Builds the table header.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup[]
   *   The structured array with the header values.
   */
  protected function buildHeader(): array {
    return [
      'plugin_id' => $this->t('ID'),
      'entity_type' => $this->t('Entity Type'),
      'bundle' => $this->t('Bundle'),
      'description' => $this->t('Description'),
      'class' => $this->t('Class'),
      'operations' => $this->t('Operations'),
    ];
  }

  /**
   * Builds all the rows for the table.
   *
   * @param array $typed_repositories
   *   The plugin definitions.
   *
   * @return array
   *   The render array for the row.
   */
  protected function buildRows(array $typed_repositories): array {
    return array_map([$this, 'buildRow'], $typed_repositories);
  }

  /**
   * Builds a row of the table.
   *
   * @param \Drupal\typed_entity\TypedRepositories\TypedRepositoryInterface $repository
   *   The repository to build the row for.
   *
   * @return array
   *   The render array.
   */
  protected function buildRow(TypedRepositoryInterface $repository): array {
    \assert($repository instanceof PluginInspectionInterface);
    $entity_type = $repository->getEntityType();
    $bundle = $repository->getBundle();
    $plugin_id = $repository->id();
    $build = [
      'plugin_id' => [
        'data' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $plugin_id,
        ],
        'class' => ['plugin-id'],
      ],
      'entity_type' => $entity_type->getLabel(),
      'bundle' => $bundle,
      'description' => [
        'data' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $repository->getPluginDefinition()['description'] ?? '',
        ],
      ],
      'class' => [
        'data' => [
          '#theme' => 'php_class_summary',
          '#class_name' => \get_class($repository),
          '#with_comment' => FALSE,
          '#with_fqn' => FALSE,
        ],
      ],
      'operations' => [
        'data' => [
          '#type' => 'operations',
          '#links' => [
            'explore' => [
              'title' => $this->t('Explore'),
              'url' => Url::fromRoute('typed_entity_ui.details', [
                'typed_entity_id' => $plugin_id,
              ]),
            ],
          ],
        ],
      ],
    ];
    BubbleableMetadata::createFromObject($entity_type)->applyTo($build);
    return $build;
  }

}
