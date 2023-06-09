<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class ModifyFile
{
    use AsAction;

    public function handle($file, $filePath, $startLine, $lineNumber): string
    {
        $originalFile = file(base_path($filePath));
        array_splice($originalFile, $startLine - 1, $lineNumber - $startLine + 1, $file);

        return implode($originalFile);
    }
}
