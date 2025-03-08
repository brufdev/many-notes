<?php

declare(strict_types=1);

it('runs all upgrade commands', function (): void {
    $this->artisan('upgrade:run')->assertExitCode(0);
});
