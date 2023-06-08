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
        //todo: needs a huge refactor

        $repository = 'gh-pipeline-test';
        $branch = 'master';
        $message = 'Test message for the commit :)';
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

        $commitResponse = $client->post("repos/{$owner}/{$repository}/git/commits", [
            'json' => [
                'message' => $message,
                'tree' => $treeSHA,
                'parents' => $this->getCommitsSHA($owner, $repository)
            ],
        ]);

        $commitSHA = json_decode($commitResponse->getBody()->getContents(), true)['sha'];

        $pushResponse = $client->patch("/repos/{$owner}/{$repository}/git/refs/heads/{$branch}", [
            'json' => [
                'sha' => $commitSHA,
                'force' => false
            ]
        ]);
        dd($pushResponse);
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
                    'content' => 'hola que tal',
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

    public function getCommitsSHA($owner, $repo): array
    {
        $client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer '.config('services.gh_token'),
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);
        $response = $client->get("https://api.github.com/repos/{$owner}/{$repo}/commits");
        $response = json_decode($response->getBody()->getContents(), true);
        if (!empty($response) && is_array($response)) {
            $commits = array_slice($response, 0, 2);

            $parents = array_map(function ($commit) {
                return $commit['sha'];
            }, $commits);
        }
        return $parents;
    }
}
