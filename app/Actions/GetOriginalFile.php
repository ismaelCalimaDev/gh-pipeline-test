<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class GetOriginalFile
{
    use AsAction;
    public string $commandSignature = 'gh:modify-file';

    public function handle(string $filePath, int $lineNumber)
    {
        $originalFile = file($filePath);
        array_splice($originalFile, $lineNumber - 1, 0);
        return json_encode($originalFile);
    }
}
