<?php

use Lemon\Lemon;
use PHPUnit\Framework\TestCase;

class LemonTest extends TestCase
{
    /** @test */
    public function it_can_create_mock()
    {
        $lemon = Lemon::createMock('foo->bar', 1);
        $this->assertEquals(1, $lemon->foo->bar);
    }

    /** @test */
    public function it_can_mock_function()
    {
        $lemon = Lemon::createMock('foo()->bar()->bob', 1);
        $this->assertEquals(1, $lemon->foo()->bar(12)->bob);
    }

    /** @test */
    public function it_can_pass_array_as_first_params()
    {
        $lemon = Lemon::createMock([
            'id' => 1,
            'foo->bar' => 2
        ]);
        $this->assertEquals(1, $lemon->id);
        $this->assertEquals(2, $lemon->foo->bar);

        $other = Lemon::createMock([
            'foo->bar()' => 2
        ]);
        $this->assertEquals(2, $other->foo->bar());
    }

    /** @test */
    public function it_will_return_empty_string_when_attribute_key_not_exists()
    {
        $lemon = Lemon::createMock([
            'id' => 1
        ]);
        $this->assertEquals('', $lemon->foo);
    }

    /** @test */
    public function it_can_invade() {
        $foo = new class {
            protected $id = 1;
            protected function foo() {
                return 'foo';
            }
        };

        $bar = Lemon::invade($foo);
        $bar->id = 2;
        $this->assertEquals(2, $bar->id);
        $this->assertEquals('foo', $bar->foo());
    }

    /** @test */
    public function can_mock_class_property() {
        $foo = Lemon::mockClass(Foo::class, [
            'id' => 2
        ]);

        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertEquals(2, $foo->id);

        $foo = Lemon::mockClass(Foo::class, [
            'foo->bar' => 2
        ]);
        $this->assertEquals(2, $foo->foo->bar);
    }

    /** @test */
    public function it_can_mock_class_with_methods() {
        $foo = Lemon::mockClass(Foo::class, [
            'name()' => 'joe',
            'foo()->bar()->bob' => 'bob'
        ]);

        $this->assertInstanceOf(Foo::class, $foo);
        $this->assertEquals('joe', $foo->name());
        $this->assertEquals('bob', $foo->foo()->bar()->bob);
    }

    /** @test */
    public function can_update_method_in_runtime() {
        $foo = Lemon::mockClass(Foo::class, [
            'name' => 'joe'
        ]);

        $foo->setMethod('getHello', function($hello) {
            return $hello .' '. $this->name;
        });

        $this->assertEquals('hi joe', $foo->getHello('hi'));
    }
}

class Foo {
    public $id = 1;
    public function name() {
        return 'name';
    }
}
