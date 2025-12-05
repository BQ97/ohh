<?php

declare(strict_types=1);

namespace App\Providers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Vips\Driver;
use League\Container\ServiceProvider\AbstractServiceProvider;

class ImageProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        return in_array($id, ['image', ImageManager::class]);
    }

    public function register(): void
    {
        $this->getContainer()->add(ImageManager::class, function () {
            $options = [
                'autoOrientation' => true,
                'decodeAnimation' => true,
                'blendingColor' => 'ffffff',
                'strip' => false,
            ];

            if (extension_loaded('ffi')) {
                return ImageManager::withDriver(new Driver($options));
            }

            if (extension_loaded('imagick')) {
                return ImageManager::imagick($options);
            }

            return ImageManager::gd($options);
        })->setAlias('image');
    }
}
