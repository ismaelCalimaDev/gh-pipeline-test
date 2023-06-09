<?php

namespace App\Actions;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ChangeFileWithGhComment
{
    use AsAction;

    public string $commandSignature = 'gh:change-file {commentContent} {filePath} {lineNumber} {repository} {owner} {branch}';

    public function handle($commentContent, $filePath, $lineNumber, $repository, $owner, $branch)
    {
        $originalFileFormatted = GetOriginalFile::run($filePath, $lineNumber);
        $openAiResponseFormatted = SendRequestToOpenAi::run($originalFileFormatted, $commentContent);
        if ($openAiResponseFormatted === null || $openAiResponseFormatted === []) {
            logger('Vacío');

            return;
        }
        $newContent = ModifyFile::run($filePath, $openAiResponseFormatted);
        PushChangesToGithub::run($repository, $branch, $filePath, $owner, $newContent);
    }

    public function asCommand(Command $command): void
    {
        $this->handle(
            $command->argument('commentContent'),
            $command->argument('filePath'),
            $command->argument('lineNumber'),
            $command->argument('repository'),
            $command->argument('owner'),
            $command->argument('branch'),
        );

        $command->info('Done!');
    }
}
