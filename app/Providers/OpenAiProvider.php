<?php

declare(strict_types=1);

namespace App\Providers;

use League\Container\ServiceProvider\AbstractServiceProvider;
use App\OpenAi;

class OpenAiProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['openai', OpenAi::class]);
    }

    public function register(): void
    {
        $env = $this->getContainer()->get('env');
        $this->getContainer()->add(OpenAi::class)->setAlias('openai')->addArgument($env->get('OPENAI_API_KEY', ''));
    }
}
