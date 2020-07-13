<?php

namespace Drupal\Tests\typed_entity\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\typed_entity\Render\TypedEntityRenderContext;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\RepositoryManager;
use Drupal\typed_entity_test\Render\Article\ConditionalRenderer;
use Drupal\typed_entity_test\Render\Article\Teaser;
use Drupal\typed_entity_test\Render\Page\Base;
use Drupal\user\Entity\User;

/**
 * Test the FieldValueVariantCondition class.
 *
 * @coversDefaultClass \Drupal\typed_entity\Render\TypedEntityRendererBase
 *
 * @group typed_entity
 */
class TypedEntityRendererTest extends KernelTestBase {

  /**
   * A test article.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $article;

  /**
   * A test entity wrapper.
   *
   * @var \Drupal\typed_entity_test\TypedRepositories\ArticleRepository
   */
  private $articleRepository;

  /**
   * A test page.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  private $page;

  /**
   * A test entity wrapper.
   *
   * @var \Drupal\typed_entity\TypedRepositories\TypedEntityRepositoryInterface
   */
  private $pageRepository;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $article = NodeType::load('article');
    $article->set('display_submitted', FALSE);
    $article->save();

    $this->article = Node::create([
      'type' => 'article',
      'title' => 'Test Article',
      'uid' => User::load(1),
    ]);
    $this->article->save();
    $this->articleRepository = \Drupal::service(RepositoryManager::class)
      ->repositoryFromEntity($this->article);

    $this->page = Node::create([
      'type' => 'page',
      'title' => 'Test Page',
      'uid' => User::load(1),
    ]);
    $this->article->save();
    $this->pageRepository = \Drupal::service(RepositoryManager::class)
      ->repositoryFromEntity($this->page);
  }

  /**
   * Tests the fallback functionality.
   */
  public function testFallback(): void {
    $context = new TypedEntityRenderContext();
    $renderers = $this->pageRepository->rendererFactory($context);
    $this->assertCount(1, $renderers);
    $this->assertInstanceOf(Base::class, current($renderers));
  }

  /**
   * Tests the fallback functionality.
   *
   * @dataProvider rendererNegotiationViewModeDataProvider
   */
  public function testRendererNegotiationViewMode(string $view_mode, string $expected_class): void {
    $context = new TypedEntityRenderContext(['view_mode' => $view_mode]);
    $renderers = $this->articleRepository->rendererFactory($context);
    $this->assertCount(1, $renderers);
    $this->assertInstanceOf($expected_class, current($renderers));
  }

  /**
   * Data provider for testRendererNegotiationViewMode.
   *
   * @return array
   *   The data.
   */
  public function rendererNegotiationViewModeDataProvider(): array {
    return [
      ['foo', TypedEntityRendererBase::class],
      [Teaser::VIEW_MODE, Teaser::class],
    ];
  }

  /**
   * Tests the fallback functionality.
   *
   * @dataProvider rendererNegotiationStateDataProvider
   */
  public function testRendererNegotiationState(bool $state, string $expected_class): void {
    $context = new TypedEntityRenderContext([
      'typed_entity_test.conditional_renderer' => $state,
    ]);
    $renderers = $this->articleRepository->rendererFactory($context);
    $this->assertCount(1, $renderers);
    $this->assertInstanceOf($expected_class, current($renderers));
  }

  /**
   * Data provider for testRendererNegotiationState.
   *
   * @return array
   *   The data.
   */
  public function rendererNegotiationStateDataProvider(): array {
    return [
      [FALSE, TypedEntityRendererBase::class],
      [TRUE, ConditionalRenderer::class],
    ];
  }

  /**
   * Tests the altering procedures.
   */
  public function testAlters(): void {
    $build = \Drupal::entityTypeManager()
      ->getViewBuilder('node')
      ->view($this->article, 'teaser');
    $renderer = \Drupal::service('renderer');
    $output = (string) $renderer->renderPlain($build);
    static::assertStringContainsString('data-variables-are-preprocessed', $output);
    static::assertSame('<h4>Altered title</h4>', $build['title']['#markup']);
  }

}
