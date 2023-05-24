<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use OpenAI\Client;
use OpenAI;

class OpenAiProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['openai', Client::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');

        $this->getContainer()->add(Client::class, fn () => OpenAI::client($env->get('OPENAI_API_KEY', '')))->setAlias('openai');
    }
}
