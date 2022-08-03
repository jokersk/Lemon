# Lemon

## Still in beta version

## Base Usage

```php

$obj = Lemon::createMock('foo->bar', 1);

$obj->foo->bar // 1

```


```php

 $lemon = Lemon::createMock('foo()->bar()->bob', 1);
 $lemon->foo()->bar(12)->bob // 1
 
```

## Mock Class


```php

class Foo {}

$foo = Lemon::mockClass(Foo::class, [
    'id' => 2
]);

$foo instanceOf Foo // true

$foo->id // 2

```

```php

class Foo {}

$foo = Lemon::mockClass(Foo::class, [
    'name()' => 'joe'
]);

$foo instanceOf Foo // true

$foo->name() // 'joe'

```
