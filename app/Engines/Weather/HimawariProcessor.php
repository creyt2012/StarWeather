<?php

namespace App\Engines\Weather;

class HimawariProcessor
{
    /**
     * Calculate cloud coverage percentage for a given tile/image.
     */
    public function calculateCloudCoverage(string $imagePath): float
    {
        if (!file_exists($imagePath)) {
            return 0.0;
        }

        $image = imagecreatefromjpeg($imagePath);
        if (!$image)
            return 0.0;

        $width = imagesx($image);
        $height = imagesy($image);
        $totalPixels = $width * $height;
        $cloudPixels = 0;

        // Simplified cloud detection: Pixels with brightness > threshold
        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Grayscale brightness
                $brightness = ($r + $g + $b) / 3;

                if ($brightness > 120) { // Threshold for cloud
                    $cloudPixels++;
                }
            }
        }

        imagedestroy($image);

        return ($cloudPixels / $totalPixels) * 100;
    }
}
