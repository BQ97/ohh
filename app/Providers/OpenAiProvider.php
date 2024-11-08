<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use OpenAI\Client;
use OpenAI;
use GuzzleHttp\Client as Guzzle;

class OpenAiProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['openai', Client::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');

        $apiKey = $env->get('OPENAI_API_KEY');

        $client = new Guzzle([
            'verify' => false,
            'proxy' => [
                'http' => 'http://127.0.0.1:7890',
                'https' => 'http://127.0.0.1:7890',
            ]
        ]);

        $this->getContainer()->add(Client::class, fn () => OpenAI::factory()->withApiKey($apiKey)->withHttpClient($client)->make())->setAlias('openai');
    }
}
