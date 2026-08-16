<?php

/**
 * The superposable images, as an allow-list.
 * Overlays live on disk rather than in the database.
 */

namespace app\models;

class Overlay
{
    private const ALL = [
        'polaroid' => 'Polaroid frame',
        'film-strip' => 'Film strip',
        'speech-bubble' => 'Speech bubble',
        'sunglasses' => 'Sunglasses',
        'mustache' => 'Mustache',
        'crown' => 'Crown',
        'party-hat' => 'Party hat',
        'cat-ears' => 'Cat ears',
        'ufo-beam' => 'UFO beam',
        'lightning' => 'Lightning',
        'rainbow' => 'Rainbow',
        'bunting' => 'Bunting',
        'confetti' => 'Confetti',
        'stars' => 'Stars',
        'bubbles' => 'Bubbles',
        'snow' => 'Snow',
        'rain' => 'Rain',
        'scanlines' => 'Scanlines',
        'spotlight' => 'Spotlight',
        'heart-mask' => 'Heart',
    ];

    public static function all(): array
    {
        return self::ALL;
    }

    public static function exists(string $key): bool
    {
        return isset(self::ALL[$key]);
    }

    public static function label(string $key): ?string
    {
        return self::ALL[$key] ?? null;
    }

    // The one place a key from the browser ever becomes a path. Anything not
    // in the list gets null, so '../../etc/passwd' never reaches the disk.
    public static function path(string $key): ?string
    {
        return self::exists($key) ? ROOT_DIR . '/public/overlays/' . $key . '.png' : null;
    }

    public static function url(string $key): string
    {
        return '/overlays/' . $key . '.png';
    }
}
