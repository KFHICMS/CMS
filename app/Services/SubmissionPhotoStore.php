<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class SubmissionPhotoStore
{
    private const MAX_SIDE = 1600;
    private const MAX_PIXELS = 25_000_000;
    private const JPEG_QUALITY = 85;

    public function disk(): string
    {
        return config('filesystems.submission_photo_disk', 'local');
    }

    /**
     * Validate, clean and store an uploaded photograph on the private disk.
     *
     * @return array{path: string, disk: string}
     *
     * @throws InvalidArgumentException when the file is not a usable photograph
     * @throws RuntimeException when it cannot be stored
     */
    public function store(UploadedFile $file, int $eventId): array
    {
        $realPath = $file->getRealPath();
        $info = $realPath ? @getimagesize($realPath) : false;

        if (! $info || ! in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            throw new InvalidArgumentException('The photo must be a JPG, PNG or WebP image.');
        }

        // Decoding a very large image can exhaust shared-hosting memory.
        if ($info[0] * $info[1] > self::MAX_PIXELS) {
            throw new InvalidArgumentException('The photo resolution is too high. Please take or choose a smaller photo.');
        }

        [$contents, $extension] = $this->normalise($realPath, $info);

        $disk = $this->disk();
        $path = 'public-submissions/'.$eventId.'/'.Str::uuid().'.'.$extension;

        if (! Storage::disk($disk)->put($path, $contents)) {
            throw new RuntimeException('The photo could not be saved.');
        }

        return ['path' => $path, 'disk' => $disk];
    }

    public function delete(?string $disk, ?string $path): void
    {
        if ($disk && $path) {
            Storage::disk($disk)->delete($path);
        }
    }

    /**
     * Re-encode as a metadata-free JPEG when GD is available.
     *
     * @return array{0: string, 1: string} contents and file extension
     */
    private function normalise(string $realPath, array $info): array
    {
        $extension = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'][$info[2]];
        $loader = [
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
        ][$info[2]];

        // Without GD the validated original is stored under a random name.
        if (! extension_loaded('gd') || ! function_exists($loader)) {
            return [file_get_contents($realPath), $extension];
        }

        $image = @$loader($realPath);

        if (! $image) {
            throw new InvalidArgumentException('The photo could not be read. Please choose another photo.');
        }

        if ($info[2] === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
            $exif = @exif_read_data($realPath);
            $rotation = match ($exif['Orientation'] ?? 1) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };

            if ($rotation) {
                $rotated = imagerotate($image, $rotation, 0);
                if ($rotated) {
                    $image = $rotated;
                }
            }
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $longest = max($width, $height);

        if ($longest > self::MAX_SIDE) {
            $scale = self::MAX_SIDE / $longest;
            $resized = imagescale($image, (int) round($width * $scale), (int) round($height * $scale));
            if ($resized) {
                $image = $resized;
            }
        }

        // Flatten transparency onto white and always output a plain JPEG.
        $canvas = imagecreatetruecolor(imagesx($image), imagesy($image));
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        imagecopy($canvas, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));

        ob_start();
        imagejpeg($canvas, null, self::JPEG_QUALITY);
        $jpeg = ob_get_clean();

        if (! is_string($jpeg) || $jpeg === '') {
            throw new RuntimeException('The photo could not be processed.');
        }

        return [$jpeg, 'jpg'];
    }
}