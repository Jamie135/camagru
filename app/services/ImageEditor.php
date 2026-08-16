<?php

/**
 * Builds the final image server-side: a picture cropped to 640x480 with an
 * overlay stamped over it. The webcam and the upload both end up here.
 */

namespace app\services;

use app\models\Overlay;
use GdImage;
use RuntimeException;

class ImageEditor
{
    public const WIDTH = 640;
    public const HEIGHT = 480;

    private const MAX_BYTES = 8 * 1024 * 1024;
    private const MAX_PIXELS = 20_000_000;
    private const MIN_SIDE = 64;
    private const QUALITY = 85;
    private const TYPES = [IMAGETYPE_JPEG, IMAGETYPE_PNG];

    public function compose(string $bytes, string $overlayKey): string
    {
        // Checked before the expensive part, so a bad key costs nothing.
        $overlay = Overlay::path($overlayKey);

        if ($overlay === null || !is_file($overlay)) {
            throw new UnusableImageException('Choose one of the overlays on the page.');
        }

        $source = $this->decode($bytes);
        $canvas = $this->cover($source);

        $this->stamp($canvas, $overlay);

        return $this->write($canvas);
    }

    private function decode(string $bytes): GdImage
    {
        if ($bytes === '') {
            throw new UnusableImageException('Take a picture, or choose a file to upload.');
        }

        if (strlen($bytes) > self::MAX_BYTES) {
            throw new UnusableImageException('That image is larger than 8 MB.');
        }

        $info = @getimagesizefromstring($bytes);

        if ($info === false || !in_array($info[2], self::TYPES, true)) {
            throw new UnusableImageException('That file is not a JPEG or a PNG.');
        }

        if ($info[0] < self::MIN_SIDE || $info[1] < self::MIN_SIDE) {
            throw new UnusableImageException('That image is too small to use.');
        }

        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new UnusableImageException('That image has too many pixels to process.');
        }

        $image = @imagecreatefromstring($bytes);

        if ($image === false) {
            throw new UnusableImageException('That image could not be read.');
        }

        return $image;
    }

    private function cover(GdImage $source): GdImage
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $ratio = self::WIDTH / self::HEIGHT;

        if ($width / $height > $ratio) {
            $cropWidth = (int) round($height * $ratio);
            $cropHeight = $height;
        } else {
            $cropWidth = $width;
            $cropHeight = (int) round($width / $ratio);
        }

        $canvas = imagecreatetruecolor(self::WIDTH, self::HEIGHT);

        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            intdiv($width - $cropWidth, 2),
            intdiv($height - $cropHeight, 2),
            self::WIDTH,
            self::HEIGHT,
            $cropWidth,
            $cropHeight
        );

        return $canvas;
    }

    private function stamp(GdImage $canvas, string $path): void
    {
        $overlay = @imagecreatefrompng($path);

        if ($overlay === false) {
            throw new RuntimeException(sprintf('Overlay %s could not be read.', $path));
        }

        imagealphablending($canvas, true);

        imagecopyresampled(
            $canvas,
            $overlay,
            0,
            0,
            0,
            0,
            self::WIDTH,
            self::HEIGHT,
            imagesx($overlay),
            imagesy($overlay)
        );
    }

    private function write(GdImage $canvas): string
    {
        $filename = bin2hex(random_bytes(16)) . '.jpg';
        $written = @imagejpeg($canvas, ROOT_DIR . '/data/uploads/' . $filename, self::QUALITY);

        if ($written === false) {
            throw new RuntimeException('Could not write ' . $filename . ' into data/uploads.');
        }

        return $filename;
    }
}
