<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

beforeEach(function (): void {
    File::partialMock()
        ->shouldReceive('get')
        ->andReturn(json_encode([
            'require' => [],
            'repositories' => [],
        ]))

        ->shouldReceive('put')
        ->andReturnTrue()

        ->shouldReceive('isDirectory')
        ->andReturnTrue()

        ->shouldReceive('directories')
        ->andReturn([
            base_path('packages/my-vendor'),
            base_path('packages/my-vendor/my-package'),
        ])

        ->shouldReceive('exists')
        ->with(base_path('packages/my-vendor/my-package/composer.json'))
        ->andReturn(true)

        ->shouldReceive('get')
        ->with(base_path('packages/my-vendor/my-package/composer.json'))
        ->andReturn(json_encode(['name' => 'my-vendor/my-package']));
});

it('runs successfully with --yes', function (): void {
    File::shouldReceive('exists')->with(base_path('composer.json'))->andReturn(true);

    $this->artisan('packages:link --yes')
        ->expectsOutput('✅ composer.json updated.')
        ->assertExitCode(0);
});

it('runs composer update with --update', function (): void {
    File::shouldReceive('exists')->with(base_path('composer.json'))->andReturn(true);

    Process::fake([
        'composer update' => Process::result(output: 'Composer updated.', exitCode: 0),
    ]);

    $this->artisan('packages:link --yes --update')
        ->expectsOutputToContain('🎼 Running `composer update`...')
        ->expectsOutputToContain('✅ Composer update completed.')
        ->assertExitCode(0);
});

it('handles missing composer.json gracefully', function (): void {
    File::shouldReceive('exists')->with(base_path('composer.json'))->andReturn(false);

    $this->artisan('packages:link --yes')
        ->expectsOutput('composer.json not found.')
        ->assertExitCode(1);
});
