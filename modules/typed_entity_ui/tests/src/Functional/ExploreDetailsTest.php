<?php

namespace Drupal\Tests\typed_entity_ui\Functional;

use Drupal\Core\Url;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the Typed Entity UI submodule integration.
 *
 * @coversDefaultClass \Drupal\typed_entity_ui\Controller\ExploreDetails
 *
 * @group typed_entity_ui
 */
class ExploreDetailsTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * An admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * Permissions to grant the admin user.
   *
   * @var array
   */
  protected $adminPermissions = [
    'access administration pages',
    'administer content types',
    'administer nodes',
    'explore typed entity classes',
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'typed_entity',
    'typed_entity_ui',
    'typed_entity_example',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->drupalCreateUser($this->adminPermissions);
    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();
  }

  /**
   * Test the detail page for the Article.
   */
  public function testDetailPageForArticle() {
    $page = $this->getSession()->getPage();
    $assert_session = $this->assertSession();
    if ($this->drupalUserIsLoggedIn($this->adminUser)) {
      $this->drupalLogout();
    }

    // Ensure the page is behind the permissions system.
    $url = Url::fromRoute(
      'typed_entity_ui.details',
      ['typed_entity_id' => 'node.article']
    );
    $this->drupalGet($url);
    $assert_session->statusCodeEquals(403);

    // The privileged user can see the page.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet($url);
    $assert_session->statusCodeEquals(200);
    $this->assertSame('Explore typed entity: Article (Content)', $page->find('css', 'h1')->getText());
    $this->assertSame('Typed Repository', $page->find('css', 'h2')->getText());
    $assert_session->pageTextContains('@TypedRepository');
    $assert_session->pageTextContains('entity_type_id = "node"');
    $assert_session->pageTextContains('bundle = "article"');
    $assert_session->pageTextContains('Variants:');
    $assert_session->pageTextContains('Fallback:');
    $assert_session->pageTextContains('final class Drupal\typed_entity_example\WrappedEntities\ BakingArticle');
    $assert_session->pageTextContains('class Drupal\typed_entity_example\Render\ Summary');
    $assert_session->pageTextContains('- None available -');

    // The user repository has no bundles and has less associated classes.
    $url = Url::fromRoute(
      'typed_entity_ui.details',
      ['typed_entity_id' => 'user']
    );
    $this->drupalGet($url);
    $assert_session->statusCodeEquals(200);
    $this->assertSame('Explore typed entity: User', $page->find('css', 'h1')->getText());
    $this->assertSame('Typed Repository', $page->find('css', 'h2')->getText());
    $assert_session->pageTextContains('@TypedRepository');
    $assert_session->pageTextContains('entity_type_id = "user"');
    $assert_session->pageTextNotContains('bundle');
    $assert_session->pageTextNotContains('Variants:');
    $assert_session->pageTextNotContains('Fallback:');
    $assert_session->pageTextContains('final class Drupal\typed_entity_example\WrappedEntities\ User');
    $assert_session->pageTextContains('- None available -');

    // What about a non existing content type?
    $url = Url::fromRoute(
      'typed_entity_ui.details',
      ['typed_entity_id' => 'node.lorem']
    );
    $this->drupalGet($url);
    $assert_session->statusCodeEquals(200);
    $this->assertSame('Explore typed entity: Content', $page->find('css', 'h1')->getText());
    $this->assertSame('Not found', $page->find('css', 'h2')->getText());
    $assert_session->pageTextContains('See the documentation to learn how to associate a typed entity repository to type of entity.');

    // What about a non existing entity type?
    $url = Url::fromRoute(
      'typed_entity_ui.details',
      ['typed_entity_id' => 'foo.bar']
    );
    $this->drupalGet($url);
    $assert_session->statusCodeEquals(200);
    $this->assertSame('Explore typed entity:', $page->find('css', 'h1')->getText());
    $this->assertSame('Not found', $page->find('css', 'h2')->getText());
    $assert_session->pageTextContains('See the documentation to learn how to associate a typed entity repository to type of entity.');
  }

}
