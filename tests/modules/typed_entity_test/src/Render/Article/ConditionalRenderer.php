<?php

namespace Drupal\typed_entity_test\Render\Article;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\typed_entity\Render\TypedEntityRendererBase;
use Drupal\typed_entity\TypedEntityContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renderer that applies depending on the server state.
 */
final class ConditionalRenderer extends TypedEntityRendererBase {

  /**
   * The state manager.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  private $state;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, StateInterface $state) {
    parent::__construct($entity_type_manager);
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function applies(TypedEntityContext $context): bool {
    return $context->offsetExists('typed_entity_test.conditional_renderer')
      && $context->offsetGet('typed_entity_test.conditional_renderer');
  }

}
