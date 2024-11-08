<?php

declare(strict_types=1);

namespace App\Response;

use League\MimeTypeDetection\GeneratedExtensionToMimeTypeMap;

/**
 * Class MimeType
 */
class MineType
{

    /**
     * Get the MIME type for a file based on the file's extension.
     *
     * @param  string  $filename
     * @return string
     */
    public static function from(string $filename)
    {
        $extension = strtok(pathinfo($filename, PATHINFO_EXTENSION), '?');

        return static::getMimeTypeFromExtension($extension);
    }

    /**
     * Get the MIME type for a given extension or return all mimes.
     *
     * @param  string  $extension
     * @return string|array
     */
    public static function get(string $extension = null)
    {
        return isset($extension) ? static::getMimeTypeFromExtension($extension) : GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS;
    }

    /**
     * Get the MIME type for a given extension.
     *
     * @param  string  $extension
     * @return string
     */
    protected static function getMimeTypeFromExtension(string $extension)
    {
        return GeneratedExtensionToMimeTypeMap::MIME_TYPES_FOR_EXTENSIONS[$extension] ?? 'application/octet-stream';
    }
}
