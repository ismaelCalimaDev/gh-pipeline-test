<?php

namespace App\Actions;

use GrahamCampbell\GitHub\Facades\GitHub;
use GuzzleHttp\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class PushChangesToGithub
{
    use AsAction;

    public string $commandSignature = 'gh:push';

    public function handle()
    {
        $repository = 'gh-pipeline-test';
        $branch = 'master';
        $message = 'Test message for the commit :)';
        $files = 'todo';
        $owner = 'ismaelCalimaDev';
        $filePath = 'routes/web.php';

        //$latestCommits = $this->getLatestCommit($repository, $branch, $owner);
        $treeSHA = $this->getTree($repository, $branch, $filePath, $owner)['sha'];

        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer '.config('services.gh_token'),
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        $response = $client->post("repos/{$owner}/{$repository}/git/commits", [
            'json' => [
                'message' => $message,
                'tree' => $treeSHA,
            ],
        ]);

        return dd($response->getBody()->getContents());
    }

    public function getTree($repository, $branch, $filePath, $owner)
    {
        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer '.config('services.gh_token'),
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        $response = $client->post("repos/{$owner}/{$repository}/git/trees", [
            'json' => [
                'base_tree' => $this->getLatestCommit($repository, $branch, $owner)[0]['commit']['tree']['sha'],
                'tree' => [[
                    'path' => $filePath,
                    'mode' => '100644',
                    'type' => 'blob',
                    'content' => base64_encode(file_get_contents($filePath)),
                ]],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    public function getLatestCommit($repository, $branch, $owner)
    {
        //https://api.github.com/repos/{$owner}/{$repo}/commits?sha={$branch}&per_page=1
        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer '.config('services.gh_token'),
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);

        $response = $client->get("repos/{$owner}/{$repository}/commits?sha={$branch}&per_page=1");

        return json_decode($response->getBody()->getContents(), true);
    }
}
