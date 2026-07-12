<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Helpers;

/**
 * File type detection helpers.
 */
class FileHelper
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];

    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'odt'];

    private const VIDEO_EXTENSIONS = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv'];

    private const AUDIO_EXTENSIONS = ['mp3', 'wav', 'aac', 'flac'];

    public static function getFileType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match (true) {
            in_array($extension, self::IMAGE_EXTENSIONS) => 'image',
            in_array($extension, self::DOCUMENT_EXTENSIONS) => 'document',
            in_array($extension, self::VIDEO_EXTENSIONS) => 'video',
            in_array($extension, self::AUDIO_EXTENSIONS) => 'audio',
            default => 'other',
        };
    }
}
