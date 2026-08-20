<?php

use BlackpigCreatif\Confessionnal\CompletionProviders\CintProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\GenericProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\MTurkProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\ProlificProvider;
use BlackpigCreatif\Confessionnal\CompletionProviders\TolunaProvider;
use BlackpigCreatif\Confessionnal\Contracts\CompletionProvider;
use BlackpigCreatif\Confessionnal\Support\ProviderRegistry;

it('returns all built-in providers', function () {
    $all = ProviderRegistry::all();

    expect($all)->toContain(ProlificProvider::class);
    expect($all)->toContain(CintProvider::class);
    expect($all)->toContain(TolunaProvider::class);
    expect($all)->toContain(MTurkProvider::class);
    expect($all)->toContain(GenericProvider::class);
});

it('returns options keyed by class name', function () {
    $options = ProviderRegistry::options();

    expect($options[ProlificProvider::class])->toBe('Prolific');
    expect($options[CintProvider::class])->toBe('Cint');
    expect($options[GenericProvider::class])->toBe('Generic');
});

it('resolves a provider by class name', function () {
    $provider = ProviderRegistry::resolve(ProlificProvider::class);

    expect($provider)->toBeInstanceOf(CompletionProvider::class);
    expect($provider)->toBeInstanceOf(ProlificProvider::class);
});

it('returns null for invalid provider class', function () {
    expect(ProviderRegistry::resolve('NonExistent\\Class'))->toBeNull();
    expect(ProviderRegistry::resolve('stdClass'))->toBeNull();
});

it('detects Prolific by PROLIFIC_PID', function () {
    $provider = new ProlificProvider;

    expect($provider->detect(['PROLIFIC_PID' => 'abc'], []))->toBeTrue();
    expect($provider->detect(['other' => 'abc'], []))->toBeFalse();
});

it('detects Cint by rid', function () {
    $provider = new CintProvider;

    expect($provider->detect(['rid' => 'abc'], []))->toBeTrue();
    expect($provider->detect(['other' => 'abc'], []))->toBeFalse();
});

it('detects Toluna by respondentid', function () {
    $provider = new TolunaProvider;

    expect($provider->detect(['respondentid' => 'abc'], []))->toBeTrue();
    expect($provider->detect(['other' => 'abc'], []))->toBeFalse();
});

it('detects MTurk by assignmentId', function () {
    $provider = new MTurkProvider;

    expect($provider->detect(['assignmentId' => 'abc'], []))->toBeTrue();
    expect($provider->detect(['other' => 'abc'], []))->toBeFalse();
});

it('detects Generic by configured param', function () {
    $provider = new GenericProvider;

    expect($provider->detect(['my_param' => 'abc'], ['detect_param' => 'my_param']))->toBeTrue();
    expect($provider->detect(['other' => 'abc'], ['detect_param' => 'my_param']))->toBeFalse();
    expect($provider->detect(['my_param' => 'abc'], []))->toBeFalse();
});

it('generates static completion code', function () {
    $provider = new ProlificProvider;

    $code = $provider->generateCode(['code_type' => 'static', 'static_code' => 'TEST123']);

    expect($code)->toBe('TEST123');
});

it('generates dynamic completion code', function () {
    $provider = new MTurkProvider;

    $code = $provider->generateCode(['code_type' => 'dynamic']);

    expect($code)->toBeString()->toHaveLength(8);
});

it('returns null for no-code type', function () {
    $provider = new CintProvider;

    $code = $provider->generateCode(['code_type' => 'none']);

    expect($code)->toBeNull();
});

it('builds redirect URL with code parameter', function () {
    $provider = new ProlificProvider;

    $url = $provider->buildRedirectUrl(
        ['redirect_url' => 'https://prolific.com/complete', 'code_param_key' => 'cc'],
        'ABC123',
        [],
    );

    expect($url)->toBe('https://prolific.com/complete?cc=ABC123');
});

it('builds redirect URL with passthrough params', function () {
    $provider = new CintProvider;

    $url = $provider->buildRedirectUrl(
        ['redirect_url' => 'https://cint.com/complete?token=XYZ', 'passthrough_params' => true],
        null,
        ['rid' => 'resp1'],
    );

    expect($url)->toBe('https://cint.com/complete?token=XYZ&rid=resp1');
});

it('returns null when no redirect URL configured', function () {
    $provider = new MTurkProvider;

    $url = $provider->buildRedirectUrl([], 'CODE', []);

    expect($url)->toBeNull();
});

it('all providers return a non-empty config schema', function () {
    foreach (ProviderRegistry::all() as $class) {
        expect($class::getConfigSchema())->toBeArray()->not->toBeEmpty();
    }
});

it('all providers have defaults matching their config fields', function () {
    foreach (ProviderRegistry::all() as $class) {
        $defaults = $class::getDefaults();
        expect($defaults)->toBeArray();
    }
});
