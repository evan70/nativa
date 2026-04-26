<?php

declare(strict_types=1);

use Marko\Database\Connection\TransactionInterface;

describe('TransactionInterface', function (): void {
    it('defines TransactionInterface with begin, commit, and rollback methods', function (): void {
        $reflection = new ReflectionClass(TransactionInterface::class);

        expect($reflection->isInterface())->toBeTrue()
            ->and($reflection->hasMethod('beginTransaction'))->toBeTrue()
            ->and($reflection->hasMethod('commit'))->toBeTrue()
            ->and($reflection->hasMethod('rollback'))->toBeTrue();

        $begin = $reflection->getMethod('beginTransaction');
        $returnType = $begin->getReturnType();
        expect($returnType instanceof ReflectionNamedType ? $returnType->getName() : null)->toBe('void');

        $commit = $reflection->getMethod('commit');
        $returnType = $commit->getReturnType();
        expect($returnType instanceof ReflectionNamedType ? $returnType->getName() : null)->toBe('void');

        $rollback = $reflection->getMethod('rollback');
        $returnType = $rollback->getReturnType();
        expect($returnType instanceof ReflectionNamedType ? $returnType->getName() : null)->toBe('void');
    });

    it('defines TransactionInterface with transaction callback method', function (): void {
        $reflection = new ReflectionClass(TransactionInterface::class);

        expect($reflection->hasMethod('transaction'))->toBeTrue();

        $transaction = $reflection->getMethod('transaction');
        $params = $transaction->getParameters();
        $returnType = $transaction->getReturnType();
        expect($returnType instanceof ReflectionNamedType ? $returnType->getName() : null)->toBe('mixed')
            ->and($params)->toHaveCount(1)
            ->and($params[0]->getName())->toBe('callback');
        
        $paramType = $params[0]->getType();
        expect($paramType instanceof ReflectionNamedType ? $paramType->getName() : null)->toBe('callable');
    });
});
