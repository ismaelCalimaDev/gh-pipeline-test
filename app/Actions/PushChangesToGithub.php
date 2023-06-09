<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class PushChangesToGithub
{
    use AsAction;

    public string $commandSignature = 'gh:push';

    public Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.github.com/',
            'headers' => [
                'Authorization' => 'Bearer '.config('services.gh_token'),
                'Accept' => 'application/vnd.github.v3+json',
            ],
        ]);
    }

    public function handle($repository, $branch, $filePath, $owner, $newContent)
    {
        $message = 'Genesis gpt commit :)';
        $treeSHA = $this->getTree($repository, $branch, $filePath, $owner, $newContent)['sha'];
        $commitParents = $this->getCommitsSHA($owner, $repository, $branch);
        $commitResponse = $this->makeCommit($owner, $repository, $message, $treeSHA, $commitParents);
        $commitSHA = $this->getNewCommitSHA($commitResponse);

        $this->client->patch("/repos/{$owner}/{$repository}/git/refs/heads/{$branch}", [
            'json' => [
                'sha' => $commitSHA,
                'force' => false,
            ],
        ]);
    }

    private function getTree($repository, $branch, $filePath, $owner, $newContent)
    {

        $response = $this->client->post("repos/{$owner}/{$repository}/git/trees", [
            'json' => [
                'base_tree' => $this->getLatestCommit($repository, $branch, $owner)[0]['commit']['tree']['sha'],
                'tree' => [[
                    'path' => $filePath,
                    'mode' => '100644',
                    'type' => 'blob',
                    'content' => $newContent,
                ]],
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getLatestCommit($repository, $branch, $owner)
    {
        $response = $this->client->get("repos/{$owner}/{$repository}/commits?sha={$branch}&per_page=1");

        return json_decode($response->getBody()->getContents(), true);
    }

    private function getCommitsSHA($owner, $repo, $branch): array
    {
        $response = $this->client->get("https://api.github.com/repos/{$owner}/{$repo}/commits?sha={$branch}");
        $response = json_decode($response->getBody()->getContents(), true);
        $parents = [];
        if (! empty($response) && is_array($response)) {
            $commits = array_slice($response, 0, 2);

            $parents = array_map(function ($commit) {
                return $commit['sha'];
            }, $commits);
        }

        return $parents;
    }

    private function makeCommit($owner, $repository, $message, $treeSHA, $commitParents)
    {
        $response = $this->client->post("repos/{$owner}/{$repository}/git/commits", [
            'json' => [
                'message' => $message,
                'tree' => $treeSHA,
                'parents' => $commitParents,
            ],
        ]);

        return $response;
    }

    private function getNewCommitSHA($commitResponse): string
    {
        return json_decode($commitResponse->getBody()->getContents(), true)['sha'];
    }
}
