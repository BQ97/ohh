<?php

declare(strict_types=1);

namespace App\Response;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Stream;
use App\Response\MineType;
use Laminas\Diactoros\Response\InjectContentTypeTrait;

class DownloadResponse extends Response
{
    use InjectContentTypeTrait;

    public function __construct(string $content, string $filename, array $headers = [])
    {
        $body = $this->createBodyFromString($content);

        $headers = $this->injectDownloadHeader(pathinfo($filename, PATHINFO_BASENAME), $headers);

        parent::__construct($body, 200, $headers);
    }

    protected function notFoundResponse()
    {
        parent::__construct(new Stream('php://temp', 'r'), 404);
    }

    protected function injectDownloadHeader(string $filename, array $headers = [])
    {
        $headers = $this->injectContentType(MineType::from($filename), $headers);

        $headers['Content-Disposition'] = 'attachment; filename=' . $filename;

        return $headers;
    }

    private function createBodyFromString(string $string): Stream
    {
        $body = new Stream('php://temp', 'wb+');
        $body->write($string);
        $body->rewind();
        return $body;
    }
}
