<?php

declare(strict_types=1);

namespace App\Providers;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use League\Container\ServiceProvider\AbstractServiceProvider;

class QrCodeProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['qrcode', QRCode::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(QRCode::class, new QRCode(new QROptions([
            'quietzoneSize' => 1,
            'outputType' => 'png',
            'outputBase64' => false,
            'readerUseImagickIfAvailable' => extension_loaded('imagick'),
        ])))->setAlias('qrcode');
    }
}
