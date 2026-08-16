<?php

/**
 * Draws the superposable images into public/overlays/.
 * docker compose exec -u www-data php php tools/make-overlays.php
 */

const WIDTH = 640;
const HEIGHT = 480;
const OUT = __DIR__ . '/../public/overlays';

// GD's own truecolor packing, built by hand to keep the drawing code short.
function rgba(int $red, int $green, int $blue, int $alpha = 0): int
{
    return ($alpha << 24) | ($red << 16) | ($green << 8) | $blue;
}

// Blending stays off throughout: these images are holes as much as they are
// paint, and a hole cannot be composited into place.
function canvas(int $scale = 1): GdImage
{
    $image = imagecreatetruecolor(WIDTH * $scale, HEIGHT * $scale);

    imagealphablending($image, false);
    imagefilledrectangle($image, 0, 0, WIDTH * $scale - 1, HEIGHT * $scale - 1, rgba(0, 0, 0, 127));

    return $image;
}

// Scales a flat list of x, y pairs from 640x480 space into the drawing canvas.
function points(array $pairs, int $scale): array
{
    return array_map(static fn (int|float $value): int => (int) round($value * $scale), $pairs);
}

function polaroid(): GdImage
{
    $image = canvas();

    imagefilledrectangle($image, 0, 0, WIDTH - 1, HEIGHT - 1, rgba(250, 248, 242));

    // The window is a hole punched clean through, not a lighter shade of frame.
    imagefilledrectangle($image, 24, 24, WIDTH - 25, HEIGHT - 85, rgba(0, 0, 0, 127));

    imagerectangle($image, 23, 23, WIDTH - 24, HEIGHT - 84, rgba(198, 192, 180));

    return $image;
}

function sunglasses(): GdImage
{
    $scale = 4;
    $image = canvas($scale);

    $rim = rgba(14, 16, 20);
    $glass = rgba(18, 22, 30, 40);

    imagefilledrectangle($image, 172 * $scale, 156 * $scale, 468 * $scale, 180 * $scale, $rim);
    imagefilledrectangle($image, 304 * $scale, 172 * $scale, 336 * $scale, 196 * $scale, $rim);

    // Temples, tapering off the side of the frame towards the ears.
    imagefilledpolygon($image, points([176, 158, 176, 184, 0, 146, 0, 124], $scale), $rim);
    imagefilledpolygon($image, points([464, 158, 464, 184, WIDTH, 146, WIDTH, 124], $scale), $rim);

    foreach ([242, 398] as $centre) {
        imagefilledellipse($image, $centre * $scale, 212 * $scale, 140 * $scale, 104 * $scale, $rim);
        imagefilledellipse($image, $centre * $scale, 212 * $scale, 120 * $scale, 84 * $scale, $glass);
    }

    return $image;
}

function ufoBeam(): GdImage
{
    $scale = 4;
    $image = canvas($scale);

    // Beam first: the hull is drawn over it, so the cone starts behind the saucer.
    imagefilledpolygon($image, points([268, 92, 372, 92, 572, HEIGHT, 68, HEIGHT], $scale), rgba(140, 250, 225, 112));
    imagefilledpolygon($image, points([296, 92, 344, 92, 452, HEIGHT, 188, HEIGHT], $scale), rgba(190, 255, 240, 96));

    imagefilledellipse($image, 320 * $scale, 58 * $scale, 124 * $scale, 88 * $scale, rgba(150, 225, 235, 44));
    imagefilledellipse($image, 320 * $scale, 84 * $scale, 260 * $scale, 68 * $scale, rgba(132, 146, 158));
    imagefilledellipse($image, 320 * $scale, 98 * $scale, 190 * $scale, 34 * $scale, rgba(74, 86, 96));

    foreach ([224, 272, 320, 368, 416] as $lamp) {
        imagefilledellipse($image, $lamp * $scale, 92 * $scale, 18 * $scale, 18 * $scale, rgba(255, 206, 92));
    }

    return $image;
}

function confetti(): GdImage
{
    $scale = 4;
    $image = canvas($scale);

    // Seeded, so re-running the tool does not silently reshuffle the artwork.
    mt_srand(20260816);

    $palette = [
        [244, 96, 122], [255, 190, 74], [96, 214, 196], [124, 158, 246],
        [186, 128, 232], [126, 214, 122], [255, 138, 92], [246, 232, 118],
    ];

    for ($piece = 0; $piece < 170; $piece++) {
        [$red, $green, $blue] = $palette[mt_rand(0, count($palette) - 1)];
        $colour = rgba($red, $green, $blue, mt_rand(0, 46));

        $x = mt_rand(0, WIDTH);

        // Biased towards the top, the way confetti thins out as it falls.
        $y = (int) round(HEIGHT * ((mt_rand(0, 1000) / 1000) ** 1.7));

        if (mt_rand(0, 4) === 0) {
            imagefilledellipse($image, $x * $scale, $y * $scale, 11 * $scale, 11 * $scale, $colour);

            continue;
        }

        $width = mt_rand(7, 15);
        $height = mt_rand(12, 26);
        $angle = mt_rand(0, 359) * M_PI / 180;

        $corners = [];

        foreach ([[-$width, -$height], [$width, -$height], [$width, $height], [-$width, $height]] as [$dx, $dy]) {
            $corners[] = $x + ($dx * cos($angle) - $dy * sin($angle)) / 2;
            $corners[] = $y + ($dx * sin($angle) + $dy * cos($angle)) / 2;
        }

        imagefilledpolygon($image, points($corners, $scale), $colour);
    }

    return $image;
}

function scanlines(): GdImage
{
    $image = canvas();

    $centreX = WIDTH / 2;
    $centreY = HEIGHT / 2;
    $furthest = sqrt($centreX ** 2 + $centreY ** 2);

    for ($y = 0; $y < HEIGHT; $y++) {
        $line = $y % 4 < 2 ? 0.26 : 0.0;

        for ($x = 0; $x < WIDTH; $x++) {
            // Vignette: flat across the middle, then a soft ramp into the corners.
            $distance = sqrt(($x - $centreX) ** 2 + ($y - $centreY) ** 2) / $furthest;
            $corner = max(0.0, ($distance - 0.58) / 0.42) ** 1.6 * 0.5;

            $opacity = min(0.62, $line + $corner);

            imagesetpixel($image, $x, $y, rgba(6, 14, 16, 127 - (int) round($opacity * 127)));
        }
    }

    return $image;
}

// Curves are drawn four times over size and resampled here, because GD's own
// antialiasing does not survive an alpha channel.
function save(GdImage $image, string $name): void
{
    if (imagesx($image) !== WIDTH) {
        $small = imagecreatetruecolor(WIDTH, HEIGHT);

        imagealphablending($small, false);
        imagecopyresampled($small, $image, 0, 0, 0, 0, WIDTH, HEIGHT, imagesx($image), imagesy($image));

        $image = $small;
    }

    imagesavealpha($image, true);

    $path = OUT . '/' . $name . '.png';

    if (!imagepng($image, $path, 9)) {
        fwrite(STDERR, sprintf("Could not write %s\n", $path));

        exit(1);
    }

    // Proves the alpha channel survived, rather than trusting that it did.
    $lowest = 127;
    $highest = 0;

    for ($y = 0; $y < HEIGHT; $y++) {
        for ($x = 0; $x < WIDTH; $x++) {
            $alpha = (imagecolorat($image, $x, $y) >> 24) & 0x7F;

            $lowest = min($lowest, $alpha);
            $highest = max($highest, $alpha);
        }
    }

    printf(
        "%-11s %dx%d   alpha %d-%d   %5.1f KB\n",
        $name,
        WIDTH,
        HEIGHT,
        $lowest,
        $highest,
        filesize($path) / 1024
    );
}

if (!is_dir(OUT) && !mkdir(OUT, 0775, true)) {
    fwrite(STDERR, sprintf("Could not create %s\n", OUT));

    exit(1);
}

foreach ([
    'polaroid' => 'polaroid',
    'sunglasses' => 'sunglasses',
    'ufo-beam' => 'ufoBeam',
    'confetti' => 'confetti',
    'scanlines' => 'scanlines',
] as $name => $draw) {
    save($draw(), $name);
}
