<?php

namespace App\Services;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use RuntimeException;

class QrCodeService
{
    private const MARKER = '{{SIGNATURE_QR_RID}}';

    public function marker(): string
    {
        return self::MARKER;
    }

    public function png(string $payload): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Extension GD dibutuhkan untuk membuat QR code.');
        }

        $this->ensureQrLibraryLoaded();

        $png = (new QRCode(new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'imageBase64' => false,
            'scale' => 8,
            'quietzoneSize' => 4,
        ])))->render($payload);

        if (! is_string($png) || ! str_starts_with($png, "\x89PNG")) {
            throw new RuntimeException('Gagal membuat QR code PNG.');
        }

        return $png;
    }

    private function ensureQrLibraryLoaded(): void
    {
        if (class_exists(QRCode::class)) {
            return;
        }

        spl_autoload_register(function (string $class): void {
            $prefixes = [
                'chillerlan\\QRCode\\' => base_path('vendor/chillerlan/php-qrcode/src/'),
                'chillerlan\\Settings\\' => base_path('vendor/chillerlan/php-settings-container/src/'),
            ];

            foreach ($prefixes as $prefix => $basePath) {
                if (! str_starts_with($class, $prefix)) {
                    continue;
                }

                $file = $basePath.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';

                if (is_file($file)) {
                    require_once $file;
                }
            }
        });

        if (! class_exists(QRCode::class)) {
            throw new RuntimeException('Library QR code belum tersedia. Jalankan composer install.');
        }
    }
}
