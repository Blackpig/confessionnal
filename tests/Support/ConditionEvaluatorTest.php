<?php

use BlackpigCreatif\Confessionnal\Support\ConditionEvaluator;

it('returns true when condition is null', function () {
    expect(ConditionEvaluator::isMet(null, ['name' => 'Alice']))->toBeTrue();
});

it('returns true when condition is empty', function () {
    expect(ConditionEvaluator::isMet([], ['name' => 'Alice']))->toBeTrue();
});

it('evaluates equals operator', function () {
    $condition = ['field_key' => 'country', 'operator' => 'equals', 'value' => 'France'];

    expect(ConditionEvaluator::isMet($condition, ['country' => 'France']))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['country' => 'UK']))->toBeFalse();
});

it('evaluates not_equals operator', function () {
    $condition = ['field_key' => 'country', 'operator' => 'not_equals', 'value' => 'France'];

    expect(ConditionEvaluator::isMet($condition, ['country' => 'UK']))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['country' => 'France']))->toBeFalse();
});

it('evaluates contains operator for strings', function () {
    $condition = ['field_key' => 'notes', 'operator' => 'contains', 'value' => 'urgent'];

    expect(ConditionEvaluator::isMet($condition, ['notes' => 'This is urgent please']))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['notes' => 'No rush']))->toBeFalse();
});

it('evaluates contains operator for arrays', function () {
    $condition = ['field_key' => 'colours', 'operator' => 'contains', 'value' => 'red'];

    expect(ConditionEvaluator::isMet($condition, ['colours' => ['red', 'blue']]))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['colours' => ['green', 'blue']]))->toBeFalse();
});

it('evaluates not_contains operator', function () {
    $condition = ['field_key' => 'colours', 'operator' => 'not_contains', 'value' => 'red'];

    expect(ConditionEvaluator::isMet($condition, ['colours' => ['green', 'blue']]))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['colours' => ['red', 'blue']]))->toBeFalse();
});

it('evaluates is_filled operator', function () {
    $condition = ['field_key' => 'email', 'operator' => 'is_filled', 'value' => null];

    expect(ConditionEvaluator::isMet($condition, ['email' => 'test@example.com']))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['email' => null]))->toBeFalse()
        ->and(ConditionEvaluator::isMet($condition, ['email' => '']))->toBeFalse()
        ->and(ConditionEvaluator::isMet($condition, []))->toBeFalse();
});

it('evaluates is_empty operator', function () {
    $condition = ['field_key' => 'email', 'operator' => 'is_empty', 'value' => null];

    expect(ConditionEvaluator::isMet($condition, ['email' => null]))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['email' => '']))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, []))->toBeTrue()
        ->and(ConditionEvaluator::isMet($condition, ['email' => 'test@example.com']))->toBeFalse();
});

it('returns true for unknown operators', function () {
    $condition = ['field_key' => 'name', 'operator' => 'banana', 'value' => 'x'];

    expect(ConditionEvaluator::isMet($condition, ['name' => 'Alice']))->toBeTrue();
});

it('returns true when field_key is missing from condition', function () {
    $condition = ['operator' => 'equals', 'value' => 'x'];

    expect(ConditionEvaluator::isMet($condition, ['name' => 'Alice']))->toBeTrue();
});
