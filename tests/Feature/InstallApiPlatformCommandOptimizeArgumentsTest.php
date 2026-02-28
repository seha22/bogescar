<?php

use Illuminate\Filesystem\Filesystem;
use Webkul\BagistoApi\Console\Commands\InstallApiPlatformCommand;

test('install command optimize arguments exclude views cache', function (): void {
    $command = new class(new Filesystem()) extends InstallApiPlatformCommand
    {
        /**
         * @return array<int, string>
         */
        public function exposedOptimizeCommandArguments(): array
        {
            return $this->optimizeCommandArguments();
        }
    };

    expect($command->exposedOptimizeCommandArguments())->toBe([
        'php',
        'artisan',
        'optimize',
        '--except=views',
    ]);
});
