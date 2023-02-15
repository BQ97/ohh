<?php

declare(strict_types=1);

namespace App;

use GuzzleHttp\Client;

class OpenAi
{
    private Client $client;

    public function __construct(string $key)
    {
        $this->client = new Client([
            'verify' => false,
            'base_uri' => 'https://api.openai.com/v1/',
            'headers' => [
                'Authorization' => 'Bearer ' . $key
            ]
        ]);
    }

    public function listModels()
    {
        return $this->client->get('models');
    }

    public function retrieveModel($model)
    {
        return $this->client->get('models/' . $model);
    }

    /**
     [
        'model' => "text-davinci-002",
        'prompt' => "西红柿炒鸡蛋怎么做",
        'temperature' => 0.9,
        'max_tokens' => 150,
        'top_p' => 1,
        'frequency_penalty' => 0,
        'presence_penalty' => 0.6,
        'stream' => true,
     ]
     */
    public function completion(array $param)
    {
        return $this->client->post('completions', [
            'json' => $param,
            'stream' => $param['stream'] ?? false
        ]);
    }

    public function createEdit(array $param)
    {
        return $this->client->post('edits', [
            'json' => $param,
        ]);
    }

    public function image(array $param)
    {
        return $this->client->post('images/generations', [
            'multipart' => $param
        ]);
    }
}
