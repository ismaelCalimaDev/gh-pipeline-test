<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class ChangeFileWithGhComment
{
    use AsAction;

    public string $commandSignature = 'gh:change-file';

    //todo: needs to recieve comment
    public function handle()
    {
        /*$commentContent = $comment['body'];
        $filePath = $comment['path'];
        $lineNumber = $comment['line'];*/

        $commentContent = 'cambiando la ruta "/" por "/home';
        $filePath = 'routes/web.php';
        $lineNumber = 20;

        $originalFileFormatted = GetOriginalFile::run($filePath, $lineNumber);
        $openAiResponseFormatted = SendRequestToOpenAi::run($originalFileFormatted, $commentContent);
        ModifyFile::run($filePath,$openAiResponseFormatted);
    }
}
