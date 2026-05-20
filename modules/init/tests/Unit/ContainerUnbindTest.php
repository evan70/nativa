<?php

declare(strict_types=1);

namespace App\Init\Container\Tests;

use App\Init\Container\Container;
use Marko\Core\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;

describe('Container unbind methods', function (): void {
    beforeEach(function (): void {
        $this->container = new Container();
    });

    it('binds and unbinds an interface', function (): void {
        $this->container->bind(TestServiceInterface::class, TestService::class);
        
        expect($this->container->has(TestServiceInterface::class))->toBe(true);
        
        $removed = $this->container->unbind(TestServiceInterface::class);
        
        expect($removed)->toBe(true);
    });

    it('returns false when unbinding non-existent binding', function (): void {
        $removed = $this->container->unbind(NonExistentClass::class);
        
        expect($removed)->toBe(false);
    });

    it('binds and unbinds singleton', function (): void {
        $this->container->singleton(TestServiceInterface::class);
        $this->container->bind(TestServiceInterface::class, TestService::class);
        
        // Get the instance
        $instance = $this->container->get(TestServiceInterface::class);
        expect($instance)->toBeInstanceOf(TestService::class);
        
        $removed = $this->container->unbindSingleton(TestServiceInterface::class);
        
        expect($removed)->toBe(true);
        // Instance should be gone
        expect($this->container->has(TestServiceInterface::class))->toBe(true); // Binding still exists
    });

    it('returns all bindings', function (): void {
        $this->container->bind(ServiceA::class, ImplA::class);
        $this->container->bind(ServiceB::class, ImplB::class);
        
        $bindings = $this->container->getBindings();
        
        expect(count($bindings))->toBe(2);
        expect(isset($bindings[ServiceA::class]))->toBe(true);
        expect(isset($bindings[ServiceB::class]))->toBe(true);
    });

    it('returns all singletons', function (): void {
        $this->container->singleton(ServiceA::class);
        $this->container->singleton(ServiceB::class);
        
        $singletons = $this->container->getSingletons();
        
        expect(count($singletons))->toBe(2);
        expect(isset($singletons[ServiceA::class]))->toBe(true);
        expect(isset($singletons[ServiceB::class]))->toBe(true);
    });

    it('unbinds both binding and singleton at once', function (): void {
        $this->container->singleton(TestServiceInterface::class);
        $this->container->bind(TestServiceInterface::class, TestService::class);
        
        // First unbind the binding
        $this->container->unbind(TestServiceInterface::class);
        $bindings = $this->container->getBindings();
        expect(isset($bindings[TestServiceInterface::class]))->toBe(false);
        
        // Then unbind the singleton
        $this->container->unbindSingleton(TestServiceInterface::class);
        $singletons = $this->container->getSingletons();
        expect(isset($singletons[TestServiceInterface::class]))->toBe(false);
    });
});

// Test fixtures
interface TestServiceInterface {}

class TestService implements TestServiceInterface {}

class TestServiceImpl extends TestService {}

interface ServiceA {}
interface ServiceB {}

class ImplA implements ServiceA {}
class ImplB implements ServiceB {}

interface NonExistentClass {}