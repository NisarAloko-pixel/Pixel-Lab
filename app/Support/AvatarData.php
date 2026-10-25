<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class AvatarData
{
    public static function fromUpload(UploadedFile $file, int $size = 256): ?string
    {
        $bytes = @file_get_contents($file->getRealPath());

        return $bytes === false ? null : self::fromBytes($bytes, $size);
    }

    public static function fromBytes(string $bytes, int $size = 256): ?string
    {
        if (function_exists('imagecreatefromstring')) {
            $src = @imagecreatefromstring($bytes);

            if ($src) {
                $w = imagesx($src);
                $h = imagesy($src);
                $side = min($w, $h);

                $dst = imagecreatetruecolor($size, $size);
                imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));
                imagecopyresampled($dst, $src, 0, 0, intdiv($w - $side, 2), intdiv($h - $side, 2), $size, $size, $side, $side);

                ob_start();
                imagejpeg($dst, null, 82);
                $jpeg = ob_get_clean();

                imagedestroy($src);
                imagedestroy($dst);

                return 'data:image/jpeg;base64,' . base64_encode($jpeg);
            }
        }

        // No image library available: keep the original only if it is small enough
        if (strlen($bytes) <= 300000) {
            $mime = @getimagesizefromstring($bytes)['mime'] ?? 'image/jpeg';

            return "data:{$mime};base64," . base64_encode($bytes);
        }

        return null;
    }
}