<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class GetOriginalFile
{
    use AsAction;

    public string $commandSignature = 'gh:modify-file';

    public function handle(string $filePath, int $lineNumber, int|null $startLine)
    {

        //todo: check when no startLine
        $originalFile = file(base_path($filePath));
        array_splice($originalFile, $lineNumber - 1, 0);

        $originalFileFormatted = [];
        foreach ($originalFile as $line => $content) {
            if($line >= ($startLine - 1) && $line <= ($lineNumber - 1)) {
                $originalFileFormatted[] = $content;
            }
        }

        return json_encode($originalFileFormatted);
    }
}
