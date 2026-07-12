<?php

declare(strict_types=1);

namespace Codenzia\ProjectEssentials\Helpers;

use Illuminate\Support\Str;

/**
 * Text formatting and display helpers.
 */
class FormatHelper
{
    public static function truncateText(?string $text, int $maxLength): string
    {
        return Str::limit($text ?? '', $maxLength);
    }

    public static function hexToRgba(string $hex, float $opacity = 1.0): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $r = str_repeat(substr($hex, 0, 1), 2);
            $g = str_repeat(substr($hex, 1, 1), 2);
            $b = str_repeat(substr($hex, 2, 1), 2);
            $hex = $r . $g . $b;
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            $hex = '000000';
        }

        $bigint = hexdec($hex);
        $r = ($bigint >> 16) & 0xFF;
        $g = ($bigint >> 8) & 0xFF;
        $b = $bigint & 0xFF;

        $opacity = max(0.0, min(1.0, (float) $opacity));

        return "rgba({$r}, {$g}, {$b}, {$opacity})";
    }

    public static function getFormattedIdString(?int $id, string $prefix = 'T'): string
    {
        if ($id === null) {
            return '';
        }

        return sprintf("#$prefix%04d", $id);
    }

    public static function getSectionIcons(): array
    {
        return [
            'heroicon-o-check-circle',
            'heroicon-o-clock',
            'heroicon-o-fire',
            'heroicon-o-inbox',
            'heroicon-o-star',
            'heroicon-o-heart',
            'heroicon-o-chart-bar',
            'heroicon-o-folder',
            'heroicon-o-flag',
            'heroicon-o-bookmark',
            'heroicon-o-tag',
            'heroicon-o-calendar',
            'heroicon-o-bell',
            'heroicon-o-briefcase',
            'heroicon-o-shield-check',
            'heroicon-o-trophy',
            'heroicon-o-gift',
            'heroicon-o-user-group',
            'heroicon-o-document',
            'heroicon-o-link',
            'heroicon-o-lock-closed',
            'heroicon-o-megaphone',
            'heroicon-o-cog',
            'heroicon-o-cube',
        ];
    }
}
