<?php

use JoshBenham\LocalPackageSync\Console\Commands\AddLocalRepositories;

it('builds a valid Composer repo entry', function (): void {
    $command = new AddLocalRepositories;
    $entry = $command->buildRepoEntry('packages/my-vendor/my-package');

    expect($entry)->toMatchArray([
        'type' => 'path',
        'url' => 'packages/my-vendor/my-package',
        'options' => ['symlink' => true],
    ]);
});
