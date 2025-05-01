<?php

namespace JoshBenham\LocalPackageSync\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class AddLocalRepositories extends Command
{
    protected $signature = 'packages:link
                            {--clean : Remove non-existent local path repositories}
                            {--yes : Automatically confirm all prompts}
                            {--update : Run composer update after linking}
                            {--path=packages : The base directory to scan for local packages}';

    protected $description = 'Link local packages as Composer path repositories with optional cleanup and automation.';

    protected array $added = [];

    protected array $skipped = [];

    protected array $removed = [];

    protected array $alreadyLinked = [];

    public function handle(): int
    {
        $composerPath = base_path('composer.json');
        $autoConfirm = $this->option('yes');
        $customPath = base_path($this->option('path'));

        if (! File::exists($composerPath)) {
            $this->error('composer.json not found.');

            return 1;
        }

        if (! File::isDirectory($customPath)) {
            $this->error("Directory '{$customPath}' does not exist.");

            return 1;
        }

        $composerJson = json_decode(File::get($composerPath), true);
        $repositories = collect($composerJson['repositories'] ?? []);
        $require = collect($composerJson['require'] ?? []);

        $localPackages = $this->getLocalPackages($customPath);
        $newRepos = collect();
        $foundPaths = [];

        $localPackages->each(function ($packagePath) use (&$newRepos, &$composerJson, &$require, &$foundPaths, $repositories, $autoConfirm): void {
            $relativePath = Str::of($packagePath)->after(base_path('/'));
            $relativePathStr = (string) $relativePath;
            $foundPaths[] = $relativePathStr;

            if (! $autoConfirm && ! $this->confirm("Register local package at '{$relativePathStr}'?", true)) {
                $this->skipped[] = $relativePathStr;

                return;
            }

            $alreadyExists = $repositories->first(fn ($repo): bool => $repo['type'] === 'path' && $repo['url'] === $relativePathStr
            );

            if ($alreadyExists) {
                $this->alreadyLinked[] = $relativePathStr;
                $newRepos->push($alreadyExists);
            } else {
                $newRepos->push($this->buildRepoEntry($relativePathStr));
                $this->added[] = $relativePathStr;
            }

            $packageComposer = json_decode(File::get("{$packagePath}/composer.json"), true);
            $packageName = $packageComposer['name'] ?? null;

            if ($packageName && ! $require->has($packageName)) {
                $composerJson['require'][$packageName] = '*';
            }
        });

        if ($this->option('clean')) {
            $this->info('🧹 Cleaning broken/missing path repositories...');
            $repositories->each(function (array $repo) use (&$newRepos, $foundPaths): void {
                if ($repo['type'] === 'path' && ! in_array($repo['url'], $foundPaths) && ! File::exists(base_path($repo['url']))) {
                    $this->removed[] = $repo['url'];

                    return;
                }

                if ($repo['type'] !== 'path') {
                    $newRepos->push($repo);
                }
            });
        } else {
            $repositories->each(function (array $repo) use (&$newRepos): void {
                if ($repo['type'] !== 'path') {
                    $newRepos->push($repo);
                }
            });
        }

        // Save composer.json
        $composerJson['repositories'] = $newRepos->unique('url')->values()->all();
        File::put(base_path('composer.json'), json_encode($composerJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('✅ composer.json updated.');
        $this->outputSummary();

        if ($this->option('update')) {
            $this->runComposerUpdate();
        } else {
            $this->line("\n💡 Tip: Run `composer update` to apply changes.");
        }

        return 0;
    }

    public function getLocalPackages(string $baseDir): Collection
    {
        return collect(File::directories($baseDir))
            ->flatMap(fn ($vendorDir) => File::directories($vendorDir))
            ->filter(fn ($packageDir) => File::exists($packageDir.'/composer.json'));
    }

    public function buildRepoEntry(string $relativePath): array
    {
        return [
            'type' => 'path',
            'url' => $relativePath,
            'options' => ['symlink' => true],
        ];
    }

    protected function outputSummary(): void
    {
        $this->line("\n📋 Summary:");
        $this->table(['Action', 'Path'], collect()
            ->concat(collect($this->added)->map(fn ($p): array => ['Added', $p]))
            ->concat(collect($this->alreadyLinked)->map(fn ($p): array => ['Already linked', $p]))
            ->concat(collect($this->skipped)->map(fn ($p): array => ['Skipped', $p]))
            ->concat(collect($this->removed)->map(fn ($p): array => ['Removed (clean)', $p]))
            ->all()
        );
    }

    protected function runComposerUpdate(): void
    {
        $this->line("\n🎼 Running `composer update`...");

        $process = Process::fromShellCommandline('composer update', base_path());
        $process->setTimeout(300);
        $process->run(function ($type, $buffer): void {
            echo $buffer;
        });

        if ($process->isSuccessful()) {
            $this->info("\n✅ Composer update completed.");
        } else {
            $this->error("\n❌ Composer update failed.");
        }
    }
}
