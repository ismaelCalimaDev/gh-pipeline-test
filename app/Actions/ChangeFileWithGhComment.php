<?php

namespace App\Actions;

use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class ChangeFileWithGhComment
{
    use AsAction;

    public string $commandSignature = 'gh:change-file {commentContent} {filePath} {lineNumber} {repository} {owner} {branch} {startLine}';

    public function handle($commentContent, $filePath, $lineNumber, $repository, $owner, $branch, $startLine)
    {
        $originalFileFormatted = GetOriginalFile::run($filePath, $lineNumber, $startLine);
        $openAiResponseFormatted = SendRequestToOpenAi::run($originalFileFormatted, $commentContent);
        if ($openAiResponseFormatted === null || $openAiResponseFormatted === []) {
            /gen

            return;
        }
        $newContent = ModifyFile::run($openAiResponseFormatted, $filePath, $startLine, $lineNumber);
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
            $command->argument('startLine'),
        );

        $command->info('Done!');
    }
}
