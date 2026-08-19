<?php

namespace BlackpigCreatif\Confessionnal\Tests;

use BlackpigCreatif\Confessionnal\ConfessionnalServiceProvider;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    use LazilyRefreshDatabase;
    use WithWorkbench;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'BlackpigCreatif\\Confessionnal\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        $providers = [
            ActionsServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            LivewireServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            ConfessionnalServiceProvider::class,
        ];

        sort($providers);

        return $providers;
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing.foreign_key_constraints', true);
    }

    protected function defineDatabaseMigrations(): void
    {
        $stubsPath = __DIR__ . '/../database/migrations';
        $tempPath = sys_get_temp_dir() . '/confessionnal_migrations_' . getmypid();

        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $migrations = [
            'create_confessionnal_forms_table',
            'create_confessionnal_sections_table',
            'create_confessionnal_form_pages_table',
            'create_confessionnal_form_fields_table',
            'create_confessionnal_submissions_table',
            'create_confessionnal_form_analytics_table',
            'create_confessionnal_page_analytics_table',
        ];

        foreach ($migrations as $i => $name) {
            $stub = "{$stubsPath}/{$name}.php.stub";

            if (file_exists($stub)) {
                $timestamp = sprintf('2024_01_01_%06d', $i);
                copy($stub, "{$tempPath}/{$timestamp}_{$name}.php");
            }
        }

        $this->loadMigrationsFrom($tempPath);
    }
}
