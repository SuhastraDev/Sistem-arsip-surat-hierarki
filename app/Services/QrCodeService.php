<?php

namespace App\Services;

use RuntimeException;

class QrCodeService
{
    private const VERSION = 5;
    private const SIZE = 37;
    private const DATA_CODEWORDS = 108;
    private const ECC_CODEWORDS = 26;
    private const MARKER = '{{SIGNATURE_QR_RID}}';

    /** @var array<int, int>|null */
    private static ?array $exp = null;

    /** @var array<int, int>|null */
    private static ?array $log = null;

    public function marker(): string
    {
        return self::MARKER;
    }

    public function png(string $payload, int $scale = 5, int $margin = 4): string
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('Extension GD dibutuhkan untuk membuat QR code.');
        }

        $matrix = $this->matrix($payload);
        $moduleCount = count($matrix);
        $imageSize = ($moduleCount + ($margin * 2)) * $scale;
        $image = imagecreatetruecolor($imageSize, $imageSize);

        if (! $image) {
            throw new RuntimeException('Gagal membuat canvas QR code.');
        }

        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 15, 23, 42);
        imagefill($image, 0, 0, $white);

        foreach ($matrix as $y => $row) {
            foreach ($row as $x => $dark) {
                if (! $dark) {
                    continue;
                }

                imagefilledrectangle(
                    $image,
                    ($x + $margin) * $scale,
                    ($y + $margin) * $scale,
                    (($x + $margin + 1) * $scale) - 1,
                    (($y + $margin + 1) * $scale) - 1,
                    $black
                );
            }
        }

        ob_start();
        imagepng($image);
        $png = ob_get_clean();
        imagedestroy($image);

        return is_string($png) ? $png : '';
    }

    /**
     * Minimal QR Model 2 encoder for byte-mode verification URLs.
     *
     * Version 5-L is enough for the system verification URL while keeping the
     * image compact enough for the signature area.
     *
     * @return array<int, array<int, bool>>
     */
    private function matrix(string $payload): array
    {
        $bytes = array_values(unpack('C*', $payload) ?: []);

        if (count($bytes) > 106) {
            throw new RuntimeException('Payload QR terlalu panjang.');
        }

        $data = $this->dataCodewords($bytes);
        $ecc = $this->reedSolomonRemainder($data, self::ECC_CODEWORDS);
        $codewords = [...$data, ...$ecc];
        $modules = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));
        $reserved = array_fill(0, self::SIZE, array_fill(0, self::SIZE, false));

        $this->drawFunctionPatterns($modules, $reserved);
        $this->drawCodewords($modules, $reserved, $codewords);
        $this->drawFormatBits($modules, $reserved);

        return $modules;
    }

    /**
     * @param array<int, int> $bytes
     * @return array<int, int>
     */
    private function dataCodewords(array $bytes): array
    {
        $bits = [0, 1, 0, 0];
        $bits = [...$bits, ...$this->intBits(count($bytes), 8)];

        foreach ($bytes as $byte) {
            $bits = [...$bits, ...$this->intBits($byte, 8)];
        }

        $capacity = self::DATA_CODEWORDS * 8;
        $bits = [...$bits, ...array_fill(0, min(4, $capacity - count($bits)), 0)];

        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        $codewords = [];

        foreach (array_chunk($bits, 8) as $chunk) {
            $value = 0;

            foreach ($chunk as $bit) {
                $value = ($value << 1) | $bit;
            }

            $codewords[] = $value;
        }

        for ($pad = 0xec; count($codewords) < self::DATA_CODEWORDS; $pad = $pad === 0xec ? 0x11 : 0xec) {
            $codewords[] = $pad;
        }

        return $codewords;
    }

    /**
     * @return array<int, int>
     */
    private function intBits(int $value, int $length): array
    {
        $bits = [];

        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }

        return $bits;
    }

    /**
     * @param array<int, int> $data
     * @return array<int, int>
     */
    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = [1];

        for ($i = 0; $i < $degree; $i++) {
            $generator[] = 0;

            for ($j = count($generator) - 1; $j > 0; $j--) {
                $generator[$j] = $generator[$j - 1] ^ $this->gfMultiply($generator[$j], self::exp($i));
            }

            $generator[0] = $this->gfMultiply($generator[0], self::exp($i));
        }

        $remainder = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;

            foreach ($generator as $index => $coefficient) {
                if ($index >= $degree) {
                    break;
                }

                $remainder[$index] ^= $this->gfMultiply($coefficient, $factor);
            }
        }

        return $remainder;
    }

    private function gfMultiply(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }

        return self::exp((self::log($x) + self::log($y)) % 255);
    }

    private static function exp(int $index): int
    {
        self::initTables();

        return self::$exp[$index];
    }

    private static function log(int $value): int
    {
        self::initTables();

        return self::$log[$value];
    }

    private static function initTables(): void
    {
        if (self::$exp !== null && self::$log !== null) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);
        $value = 1;

        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $value;
            self::$log[$value] = $i;
            $value <<= 1;

            if (($value & 0x100) !== 0) {
                $value ^= 0x11d;
            }
        }

        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawFunctionPatterns(array &$modules, array &$reserved): void
    {
        $this->drawFinder($modules, $reserved, 3, 3);
        $this->drawFinder($modules, $reserved, self::SIZE - 4, 3);
        $this->drawFinder($modules, $reserved, 3, self::SIZE - 4);
        $this->drawAlignment($modules, $reserved, 30, 30);

        for ($i = 0; $i < self::SIZE; $i++) {
            $this->setFunction($modules, $reserved, 6, $i, $i % 2 === 0);
            $this->setFunction($modules, $reserved, $i, 6, $i % 2 === 0);
        }

        $this->setFunction($modules, $reserved, 8, self::SIZE - 8, true);

        for ($i = 0; $i < 9; $i++) {
            if ($i !== 6) {
                $this->setFunction($modules, $reserved, 8, $i, false);
                $this->setFunction($modules, $reserved, $i, 8, false);
            }
        }

        for ($i = self::SIZE - 8; $i < self::SIZE; $i++) {
            $this->setFunction($modules, $reserved, 8, $i, false);
            $this->setFunction($modules, $reserved, $i, 8, false);
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawFinder(array &$modules, array &$reserved, int $centerX, int $centerY): void
    {
        for ($dy = -4; $dy <= 4; $dy++) {
            for ($dx = -4; $dx <= 4; $dx++) {
                $x = $centerX + $dx;
                $y = $centerY + $dy;

                if ($x < 0 || $x >= self::SIZE || $y < 0 || $y >= self::SIZE) {
                    continue;
                }

                $distance = max(abs($dx), abs($dy));
                $this->setFunction($modules, $reserved, $x, $y, $distance !== 2 && $distance !== 4);
            }
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawAlignment(array &$modules, array &$reserved, int $centerX, int $centerY): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $distance = max(abs($dx), abs($dy));
                $this->setFunction($modules, $reserved, $centerX + $dx, $centerY + $dy, $distance !== 1);
            }
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function setFunction(array &$modules, array &$reserved, int $x, int $y, bool $dark): void
    {
        $modules[$y][$x] = $dark;
        $reserved[$y][$x] = true;
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     * @param array<int, int> $codewords
     */
    private function drawCodewords(array &$modules, array $reserved, array $codewords): void
    {
        $bits = [];

        foreach ($codewords as $codeword) {
            $bits = [...$bits, ...$this->intBits($codeword, 8)];
        }

        $index = 0;
        $upward = true;

        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right--;
            }

            for ($vertical = 0; $vertical < self::SIZE; $vertical++) {
                $y = $upward ? self::SIZE - 1 - $vertical : $vertical;

                for ($column = 0; $column < 2; $column++) {
                    $x = $right - $column;

                    if ($reserved[$y][$x]) {
                        continue;
                    }

                    $bit = ($bits[$index] ?? 0) === 1;
                    $modules[$y][$x] = $bit ^ (($x + $y) % 2 === 0);
                    $index++;
                }
            }

            $upward = ! $upward;
        }
    }

    /**
     * @param array<int, array<int, bool>> $modules
     * @param array<int, array<int, bool>> $reserved
     */
    private function drawFormatBits(array &$modules, array &$reserved): void
    {
        $mask = 0;
        $eccLevelBits = 1;
        $data = ($eccLevelBits << 3) | $mask;
        $remainder = $data;

        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ ((($remainder >> 9) & 1) ? 0x537 : 0);
        }

        $bits = (($data << 10) | $remainder) ^ 0x5412;

        for ($i = 0; $i <= 14; $i++) {
            $dark = (($bits >> $i) & 1) === 1;

            if ($i < 6) {
                $this->setFunction($modules, $reserved, 8, $i, $dark);
            } elseif ($i < 8) {
                $this->setFunction($modules, $reserved, 8, $i + 1, $dark);
            } else {
                $this->setFunction($modules, $reserved, 8, self::SIZE - 15 + $i, $dark);
            }

            if ($i < 8) {
                $this->setFunction($modules, $reserved, self::SIZE - 1 - $i, 8, $dark);
            } elseif ($i < 9) {
                $this->setFunction($modules, $reserved, 15 - $i, 8, $dark);
            } else {
                $this->setFunction($modules, $reserved, 14 - $i, 8, $dark);
            }
        }
    }
}
