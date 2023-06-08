<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class ModifyFile
{
    use AsAction;

    public function handle(string $filePath, $file): string
    {
        return implode('', $file);
    }
}
