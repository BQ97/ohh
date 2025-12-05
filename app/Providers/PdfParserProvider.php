<?php

declare(strict_types=1);

namespace App\Providers;

use Smalot\PdfParser\Parser;
use Smalot\PdfParser\Config;
use League\Container\ServiceProvider\AbstractServiceProvider;

class PdfParserProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['pdfparser', Parser::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(Parser::class, function () {
            $pdfConf = new Config();
            $pdfConf->setDataTmFontInfoHasToBeIncluded(true);
            return new Parser([], $pdfConf);
        })->setAlias('pdfparser');
    }
}
