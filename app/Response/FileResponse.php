<?php

declare(strict_types=1);

namespace App\Response;

class FileResponse extends DownloadResponse
{
    public function __construct(string $file, string $name = null, array $headers = [])
    {
        if (file_exists($file)) {
            $content = file_get_contents($file);

            $filename = $name ?: pathinfo($file, PATHINFO_BASENAME);

            parent::__construct($content, $filename, $headers);
        } else {
            parent::notFoundResponse();
        }
    }
}
