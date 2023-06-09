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
        $content = 'Te paso un array con este formato: '.$originalFileFormatted.'. Esto simboliza un archivo php y quiero que devuelvas SOLO el mismo array: '.$action;
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
                        'content' => $content,
                    ],
                ],
            ],
        ]);
        $result = json_decode($response->getBody()->getContents());
        $allResponse = $result->choices[0]->message->content;
        preg_match("/\[(.*?)\]/", $allResponse, $realResponse);

        if (! array_key_exists(0, $realResponse)) {
            return [];
        }

        return json_decode($realResponse[0]);
    }
}
