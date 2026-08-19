<?php

use BlackpigCreatif\Confessionnal\Concerns\HasSubmissionMapping;
use BlackpigCreatif\Confessionnal\Contracts\CanReceiveSubmissions;
use BlackpigCreatif\Confessionnal\Enums\FormMode;
use BlackpigCreatif\Confessionnal\Models\Form;
use BlackpigCreatif\Confessionnal\Models\Submission;
use BlackpigCreatif\Confessionnal\Support\ModelDiscovery;
use BlackpigCreatif\Confessionnal\Support\TargetModelMapper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('target_contacts', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email');
        $table->string('phone')->nullable();
        $table->timestamps();
    });
});

afterEach(function () {
    Schema::dropIfExists('target_contacts');
    Schema::dropIfExists('target_prefs');
});

function createMappedForm(string $mode = 'create', ?string $findBy = null): Form
{
    return Form::create([
        'name' => 'Mapped Form',
        'slug' => 'mapped-form',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [
            'target_model' => MappableContact::class,
            'field_mapping' => [
                ['field_key' => 'full_name', 'model_column' => 'name'],
                ['field_key' => 'email', 'model_column' => 'email'],
                ['field_key' => 'phone', 'model_column' => 'phone'],
            ],
            'target_mode' => $mode,
            'target_find_by' => $findBy,
        ],
    ]);
}

function createSubmissionFor(Form $form, array $answers): Submission
{
    return Submission::create([
        'form_id' => $form->id,
        'answers' => $answers,
        'locale' => 'en',
        'completed_at' => now(),
    ]);
}

// --- TargetModelMapper ---

it('creates a target model record on submit', function () {
    $form = createMappedForm();
    $submission = createSubmissionFor($form, [
        'full_name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '0123456789',
    ]);

    $result = TargetModelMapper::handle($form, $submission);

    expect($result)->toBeInstanceOf(MappableContact::class)
        ->and($result->name)->toBe('Alice')
        ->and($result->email)->toBe('alice@example.com')
        ->and($result->phone)->toBe('0123456789')
        ->and(MappableContact::count())->toBe(1);
});

it('upserts when target mode is update', function () {
    $form = createMappedForm('update', 'email');

    $submission1 = createSubmissionFor($form, [
        'full_name' => 'Alice',
        'email' => 'alice@example.com',
        'phone' => '111',
    ]);
    TargetModelMapper::handle($form, $submission1);

    expect(MappableContact::count())->toBe(1);

    $submission2 = createSubmissionFor($form, [
        'full_name' => 'Alice Updated',
        'email' => 'alice@example.com',
        'phone' => '222',
    ]);
    TargetModelMapper::handle($form, $submission2);

    expect(MappableContact::count())->toBe(1)
        ->and(MappableContact::first()->name)->toBe('Alice Updated')
        ->and(MappableContact::first()->phone)->toBe('222');
});

it('returns null when no target model configured', function () {
    $form = Form::create([
        'name' => 'No Mapping',
        'slug' => 'no-mapping',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [],
    ]);

    $submission = createSubmissionFor($form, ['full_name' => 'Bob']);

    expect(TargetModelMapper::handle($form, $submission))->toBeNull();
});

it('returns null when target class does not exist', function () {
    $form = Form::create([
        'name' => 'Bad Class',
        'slug' => 'bad-class',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [
            'target_model' => 'App\\Models\\NonExistent',
            'field_mapping' => [
                ['field_key' => 'name', 'model_column' => 'name'],
            ],
        ],
    ]);

    $submission = createSubmissionFor($form, ['name' => 'Bob']);

    expect(TargetModelMapper::handle($form, $submission))->toBeNull();
});

it('handles missing answer keys gracefully', function () {
    $form = createMappedForm();
    $submission = createSubmissionFor($form, [
        'full_name' => 'Charlie',
        'email' => 'charlie@example.com',
    ]);

    $result = TargetModelMapper::handle($form, $submission);

    expect($result->name)->toBe('Charlie')
        ->and($result->email)->toBe('charlie@example.com')
        ->and($result->phone)->toBeNull();
});

it('flattens array values to comma-separated strings', function () {
    Schema::create('target_prefs', function (Blueprint $table) {
        $table->id();
        $table->string('colours');
        $table->timestamps();
    });

    $form = Form::create([
        'name' => 'Array Test',
        'slug' => 'array-test',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [
            'target_model' => MappablePref::class,
            'field_mapping' => [
                ['field_key' => 'fav_colours', 'model_column' => 'colours'],
            ],
            'target_mode' => 'create',
        ],
    ]);

    $submission = createSubmissionFor($form, [
        'fav_colours' => ['red', 'blue', 'green'],
    ]);

    $result = TargetModelMapper::handle($form, $submission);

    expect($result->colours)->toBe('red, blue, green');
});

it('returns null when field mapping is empty', function () {
    $form = Form::create([
        'name' => 'Empty Mapping',
        'slug' => 'empty-mapping',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [
            'target_model' => MappableContact::class,
            'field_mapping' => [],
        ],
    ]);

    $submission = createSubmissionFor($form, ['name' => 'Bob']);

    expect(TargetModelMapper::handle($form, $submission))->toBeNull();
});

it('skips mapping rows with missing field_key or model_column', function () {
    $form = Form::create([
        'name' => 'Partial Mapping',
        'slug' => 'partial-mapping',
        'mode' => FormMode::STANDARD,
        'is_published' => true,
        'settings' => [
            'target_model' => MappableContact::class,
            'field_mapping' => [
                ['field_key' => 'full_name', 'model_column' => 'name'],
                ['field_key' => 'email_address', 'model_column' => 'email'],
                ['field_key' => 'phone'],  // missing model_column, should be skipped
                ['model_column' => 'phone'],  // missing field_key, should be skipped
            ],
            'target_mode' => 'create',
        ],
    ]);

    $submission = createSubmissionFor($form, [
        'full_name' => 'Diana',
        'email_address' => 'diana@example.com',
        'phone' => '999',
    ]);

    $result = TargetModelMapper::handle($form, $submission);

    // Only the two complete mappings should have been applied
    expect($result->name)->toBe('Diana')
        ->and($result->email)->toBe('diana@example.com')
        ->and($result->phone)->toBeNull()
        ->and(MappableContact::count())->toBe(1);
});

// --- Contract & Trait ---

it('provides a default mapping name from the class basename', function () {
    expect(MappableContact::getMappingName())->toBe('MappableContact');
});

it('auto-derives mappable columns excluding common fields', function () {
    $columns = MappableContact::getMappableColumns();

    expect($columns)->toHaveKeys(['name', 'email', 'phone'])
        ->and($columns)->not->toHaveKey('id')
        ->and($columns)->not->toHaveKey('created_at')
        ->and($columns)->not->toHaveKey('updated_at')
        ->and($columns['name'])->toBe('Name')
        ->and($columns['email'])->toBe('Email');
});

it('uses explicit $mappable property when defined', function () {
    $columns = CuratedContact::getMappableColumns();

    expect($columns)->toHaveCount(2)
        ->and($columns)->toHaveKeys(['name', 'email'])
        ->and($columns)->not->toHaveKey('phone');
});

// --- ModelDiscovery ---

it('discovers models registered via config', function () {
    config()->set('confessionnal.mappable_models', [MappableContact::class]);

    $models = ModelDiscovery::discover();

    expect($models)->toHaveKey(MappableContact::class)
        ->and($models[MappableContact::class])->toBe('MappableContact');
});

it('ignores non-existent classes in config', function () {
    config()->set('confessionnal.mappable_models', [
        'App\\Models\\DoesNotExist',
        MappableContact::class,
    ]);

    $models = ModelDiscovery::discover();

    expect($models)->toHaveCount(1)
        ->and($models)->toHaveKey(MappableContact::class);
});

it('ignores models that do not implement the contract', function () {
    config()->set('confessionnal.mappable_models', [
        MappablePref::class,  // implements CanReceiveSubmissions
        NonMappableModel::class,  // does NOT implement
    ]);

    Schema::create('target_prefs', function (Blueprint $table) {
        $table->id();
        $table->string('colours');
        $table->timestamps();
    });

    $models = ModelDiscovery::discover();

    expect($models)->toHaveKey(MappablePref::class)
        ->and($models)->not->toHaveKey(NonMappableModel::class);
});

// --- Test model stubs ---

class MappableContact extends Model implements CanReceiveSubmissions
{
    use HasSubmissionMapping;

    protected $table = 'target_contacts';

    protected $guarded = [];
}

class MappablePref extends Model implements CanReceiveSubmissions
{
    use HasSubmissionMapping;

    protected $table = 'target_prefs';

    protected $guarded = [];
}

class CuratedContact extends Model implements CanReceiveSubmissions
{
    use HasSubmissionMapping;

    protected $table = 'target_contacts';

    protected $guarded = [];

    public array $mappable = ['name', 'email'];
}

class NonMappableModel extends Model
{
    protected $table = 'target_contacts';

    protected $guarded = [];
}
