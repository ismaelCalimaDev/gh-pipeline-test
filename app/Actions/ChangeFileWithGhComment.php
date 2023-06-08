<?php

namespace App\Actions;

use Lorisleiva\Actions\Concerns\AsAction;

class ChangeFileWithGhComment
{
    use AsAction;

    public string $commandSignature = 'gh:change-file';

    //todo: needs to recieve comment
    public function handle($comment)
    {
        /*$commentContent = $comment['body'];
        $filePath = $comment['path'];
        $lineNumber = $comment['line'];*/

        $commentContent = $comment['body'];
        $filePath = $comment['path'];
        $lineNumber = $comment['line'];
        $repository = 'gh-pipeline-test';
        $branch = 'master';
        $owner = 'ismaelCalimaDev';

        logger($commentContent, $filePath, $lineNumber, $repository, $branch, $owner);
        dd($commentContent, $filePath, $lineNumber, $repository, $branch, $owner);

        $originalFileFormatted = GetOriginalFile::run($filePath, $lineNumber);
        $openAiResponseFormatted = SendRequestToOpenAi::run($originalFileFormatted, $commentContent);
        $newContent = ModifyFile::run($filePath, $openAiResponseFormatted);
        PushChangesToGithub::run($repository, $branch, $filePath, $owner, $newContent);
    }
}
