<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JoshBenham\LocalPackageSync\Console\Commands\AddLocalRepositories;

beforeEach(function (): void {
    File::shouldReceive('directories')
        ->with(base_path('packages'))
        ->andReturn([
            base_path('packages/my-vendor'),
        ])

        ->shouldReceive('directories')
        ->with(base_path('packages/my-vendor'))
        ->andReturn([
            base_path('packages/my-vendor/my-package'),
        ])

        ->shouldReceive('exists')
        ->with(base_path('packages/my-vendor/my-package/composer.json'))
        ->andReturn(true);
});

it('returns local packages with composer.json', function (): void {
    $command = new AddLocalRepositories;

    $packages = $command->getLocalPackages(base_path('packages'));

    expect($packages)->toBeInstanceOf(Collection::class)
        ->and($packages->first())->toContain('my-package');
});
