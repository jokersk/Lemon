# Lemon

## Still in beta version

## Install

```
composer require jokersk/lemon --dev
```

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

### Override class method

```php
class Foo {
   public $name = 'joe';
   public function name() {
      return 'some one';
   }
}

$foo = Lemon::mockClass(Foo::class, [
    'name()' => ''
]);

$foo->setMethod('name', function() {
 return $this->name; <-- $this is pointing to Foo
});

$foo->name() // 'joe'
```
