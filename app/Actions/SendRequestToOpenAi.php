<?php

namespace App\Actions;

use GuzzleHttp\Client;
use Lorisleiva\Actions\Concerns\AsAction;

class SendRequestToOpenAi
{
    use AsAction;

    public string $commandSignature = 'openai:send-request';

    public function handle(string $originalFileFormatted, string $action): array|null
    {
        $prompt = 'I am giving to you an array with this format: '.$originalFileFormatted.'. Each /n means a new line in the file and I want you give me back the same format but: '.$action;
        $response = $this->makeOpenAIRequest($prompt);
        $formattedResponse = $this->formatResponse($response->choices[0]->message->content);

        if (! array_key_exists(0, $formattedResponse)) {
            return [];
        }

        return json_decode($formattedResponse[0]);
    }

    private function makeOpenAIRequest(string $prompt)
    {
        $client = new Client();
        $response = $client->request('POST', 'https://api.openai.com/v1/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.config('services.openai_token'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
            ],
        ]);
        return json_decode($response->getBody()->getContents());
    }

    private function formatResponse($content)
    {
        preg_match("/\[(.*?)\]/", $content, $formattedResponse);
        return $formattedResponse;
    }
}
