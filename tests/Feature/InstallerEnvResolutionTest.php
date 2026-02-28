<?php

use Webkul\Installer\Console\Commands\Installer;

test('installer prioritizes runtime environment values over env file parsing', function (): void {
    putenv('CODEX_INSTALLER_TEST_KEY=runtime-value');

    $command = new class extends Installer
    {
        public function exposedGetEnvVariable(string $key, mixed $default = null): string|bool
        {
            return $this->getEnvVariable($key, $default);
        }
    };

    expect($command->exposedGetEnvVariable('CODEX_INSTALLER_TEST_KEY', 'fallback'))->toBe('runtime-value');

    putenv('CODEX_INSTALLER_TEST_KEY');
});
