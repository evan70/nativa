<?php

declare(strict_types=1);


test('array_first returns first element without callback', function () {
    expect(array_first(['a', 'b', 'c']))->toBe('a');
});

test('array_first returns first element passing callback', function () {
    expect(array_first([1, 2, 3, 4], fn ($n) => $n > 2))->toBe(3);
});

test('array_first returns default if no match', function () {
    expect(array_first([1, 2], fn ($n) => $n > 5, 'default'))->toBe('default');
});

test('array_first returns default for empty array', function () {
    expect(array_first([], null, 'default'))->toBe('default');
});

test('array_last returns last element without callback', function () {
    expect(array_last(['a', 'b', 'c']))->toBe('c');
});

test('array_last returns last element passing callback', function () {
    expect(array_last([1, 2, 3, 4], fn ($n) => $n < 4))->toBe(3);
});

test('array_last returns default if no match', function () {
    expect(array_last([1, 2], fn ($n) => $n > 5, 'default'))->toBe('default');
});

test('array_last returns default for empty array', function () {
    expect(array_last([], null, 'default'))->toBe('default');
});
