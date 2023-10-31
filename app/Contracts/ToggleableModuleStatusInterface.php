<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface ToggleableModuleStatusInterface
{
    public function adminPermissions(): array;

    public function activated(): void;

    public static function permissions(): Collection;

    public function deactivationEventClass(): ?string;

    public function deactivationMessages(): array;
}
