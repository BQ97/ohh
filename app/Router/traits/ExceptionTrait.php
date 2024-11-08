<?php

declare(strict_types=1);

namespace App\Router\traits;

use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;
use App\Logger;

trait ExceptionTrait
{
    protected function getExceptionResponse(\Throwable $e, ServerRequestInterface $request)
    {
        $exceptionInfos = $this->getExceptionInfos($e);

        Logger::error($e->getMessage(), $exceptionInfos, 'error');

        if (in_array('application/json', $request->getHeader('expect'), true)) {
            return new JsonResponse($exceptionInfos, 500, [], JSON_UNESCAPED_UNICODE);
        }

        return view('exception', $exceptionInfos, 500);
    }

    protected function getExceptionInfos($e)
    {
        if ($e instanceof \ErrorException) {
            return $this->handleErrors($e);
        }

        return $this->formatExceptions($e);
    }

    protected function getSourceCode($exception)
    {
        // 读取前9行和后9行
        $line  = $exception->getLine();
        $first = ($line - 9 > 0) ? $line - 9 : 1;

        try {
            $contents = file($exception->getFile());
            $source   = [
                'first'  => $first,
                'source' => array_slice($contents, $first - 1, 19),
            ];
        } catch (\Throwable $e) {
            $source = [];
        }

        return $source;
    }

    protected function handleErrors(\ErrorException $e)
    {
        $severity = $this->determineSeverityTextValue($e->getSeverity());
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        $error = [
            'message' => $message,
            'severity' => $severity,
            'file' => $file,
            'line' => $line,
        ];
        return $error;
    }

    protected function formatExceptions($e)
    {
        $type = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTrace();

        $error = [
            'severity' => 'Exception',
            'type' => $type,
            'code' => $e->getCode(),
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'source' => $this->getSourceCode($e),
            'trace' => $trace,
        ];

        if ($e->getPrevious()) {
            $error = [$error];
            $newError = $this->formatExceptions($e->getPrevious());
            array_unshift($error, $newError);
        }

        return $error;
    }

    protected function determineSeverityTextValue($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_USER_ERROR:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
                $severity = 'Fatal Error';
                break;

            case E_PARSE:
                $severity = 'Parse Error';
                break;

            case E_WARNING:
            case E_USER_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
                $severity = 'Warning';
                break;

            case E_NOTICE:
            case E_USER_NOTICE:
                $severity = 'Notice';
                break;

            case E_STRICT:
                $severity = 'Strict Standards';
                break;

            case E_RECOVERABLE_ERROR:
                $severity = 'Catchable Error';
                break;

            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                $severity = 'Deprecated';
                break;

            default:
                $severity = 'Unknown Error';
        }

        return $severity;
    }
}
