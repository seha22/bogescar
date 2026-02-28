<?php

use Illuminate\Support\Facades\View;
use Illuminate\View\FileViewFinder;
use Tests\TestCase;

uses(TestCase::class);

test('all view namespace hint paths exist', function (): void {
    $finder = View::getFinder();

    expect($finder)->toBeInstanceOf(FileViewFinder::class);

    /** @var FileViewFinder $finder */
    $missingPaths = collect($finder->getHints())
        ->flatMap(static function (array $paths, string $namespace): array {
            return collect($paths)
                ->filter(static fn ($path): bool => ! is_string($path) || ! is_dir($path))
                ->map(static fn ($path): string => sprintf('%s => %s', $namespace, (string) $path))
                ->all();
        })
        ->values()
        ->all();

    expect($missingPaths)->toBe([]);
});
