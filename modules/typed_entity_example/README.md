# Typed Entity Example

In order to test this easily you can enable _Typed Entity Example_ in an Umami installation.

## Check the renderer

The `Drupal\typed_entity_example\Render\Article\Full` contains a simple change to turn the background cyan. You can
navigate to an article page and see that it has a cyan background.

```php
$variables['attributes']['style'] = 'background-color: cyan;';
```

Your render requirements will be more complex, but this serves as a good visual starting point. Notice how easy it was
to test this requirement via unit tests in `Drupal\Tests\typed_entity_example\Unit\Render\Article\FullTest`.

```php
  public function testPreprocess() {
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $fake_wrapped_entity = $this->prophesize(WrappedEntityInterface::class);
    $renderer = new Full($entity_type_manager->reveal());
    $build = [];
    $renderer->preprocess($build, $fake_wrapped_entity->reveal());
    $this->assertSame('background-color: cyan;', $build['attributes']['style'] ?? NULL);
  }
```

## Check the wrapped entities

The `Drupal\typed_entity_example\WrappedEntities\User` contains a method that calculates the user's nickname based on
their registration email. You can test this requirement easily, as seen in
`Drupal\Tests\types_entity_example\Unit\WrappedEntities\UserTest`.

```php
  public function testNickname() {
    $user_entity = $this->prophesize(EntityInterface::class);
    $user_entity->mail = (object) ['value' => 'foo@lorem.ipsum'];
    $sut = new User($user_entity->reveal());
    $this->assertSame('foo', $sut->nickname());
  }
```

Now turn your attention to a more complex requirement.

> Articles should return access denied when there are more than 8 published articles.

This is declared in the appropriate hook in `typed_entity_example.module`.

```php
  // ...
  if (!$repository instanceof AccessibleInterface) {
    return AccessResult::neutral();
  }
  $access = $repository->access($operation, $account, TRUE);
  // ...
```

Then implemented in the `ArticleRepository` as:

```php
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return $this->countPublishedEntities() > 8
      ? AccessResult::forbidden('More than eight articles is forbidden.')
      : AccessResult::neutral();
  }
```
