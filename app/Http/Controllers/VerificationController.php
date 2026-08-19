<?php

namespace App\Http\Controllers;

use App\Models\DigitalSignature;
use App\Models\VerificationLog;
use App\Services\DigitalSignatureService;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class VerificationController extends Controller
{
    public function __construct(
        private readonly DigitalSignatureService $digitalSignatureService,
        private readonly QrCodeService $qrCodeService
    ) {}

    public function index(Request $request)
    {
        $result = null;

        if ($request->filled('kode')) {
            $result = $this->check((string) $request->query('kode'), null);
        }

        return view('verifikasi.index', compact('result'));
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:20'],
            'dokumen' => ['nullable', 'file', 'mimes:pdf,docx', 'max:5120'],
        ]);

        $result = $this->check($data['kode'], $request->file('dokumen'));

        return view('verifikasi.index', compact('result'));
    }

    public function show(string $code)
    {
        $result = $this->check($code, null);

        return view('verifikasi.index', compact('result'));
    }

    public function qr(string $code)
    {
        $normalizedCode = strtoupper(trim($code));
        $payload = route('verification.show', $normalizedCode);

        return response($this->qrCodeService->png($payload), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function check(string $code, ?UploadedFile $file): array
    {
        $normalizedCode = strtoupper(trim($code));
        $signature = DigitalSignature::with(['pengajuanSurat.jenisSurat', 'pengajuanSurat.pemohon', 'signer'])
            ->where('verification_code', $normalizedCode)
            ->first();

        if (! $signature) {
            VerificationLog::create([
                'verification_code' => $normalizedCode,
                'status' => 'not_found',
                'uploaded_file_name' => $file?->getClientOriginalName(),
                'uploaded_file_hash' => $file ? hash_file('sha512', $file->getRealPath()) : null,
                'metadata' => ['reason' => 'Kode verifikasi tidak ditemukan.'],
            ]);

            return [
                'status' => 'invalid',
                'title' => 'Kode verifikasi tidak ditemukan',
                'message' => 'Pastikan kode yang dimasukkan sama seperti yang tertera pada detail dokumen.',
                'code' => $normalizedCode,
                'signature' => null,
                'file' => null,
            ];
        }

        $signatureValid = $this->digitalSignatureService->verify($signature->pengajuanSurat);
        $fileResult = $file ? $this->verifyFile($signature, $file) : null;
        $isValid = $signatureValid && (! $fileResult || $fileResult['matched']);

        VerificationLog::create([
            'digital_signature_id' => $signature->id,
            'verification_code' => $normalizedCode,
            'status' => $isValid ? 'valid' : 'invalid',
            'uploaded_file_name' => $file?->getClientOriginalName(),
            'uploaded_file_hash' => $fileResult['hash'] ?? null,
            'metadata' => [
                'signature_valid' => $signatureValid,
                'file_result' => $fileResult,
            ],
        ]);

        return [
            'status' => $isValid ? 'valid' : 'invalid',
            'title' => $isValid ? 'Dokumen valid' : 'Dokumen tidak valid',
            'message' => $this->message($signatureValid, $fileResult),
            'code' => $normalizedCode,
            'signature' => $signature,
            'file' => $fileResult,
        ];
    }

    private function verifyFile(DigitalSignature $signature, UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $hash = hash_file('sha512', $file->getRealPath());
        $expectedHash = $signature->metadata['file_hashes'][$extension] ?? null;

        return [
            'name' => $file->getClientOriginalName(),
            'type' => $extension,
            'hash' => $hash,
            'expected_hash' => $expectedHash,
            'matched' => is_string($expectedHash) && hash_equals($expectedHash, $hash),
        ];
    }

    private function message(bool $signatureValid, ?array $fileResult): string
    {
        if (! $signatureValid) {
            return 'Tanda tangan digital tidak cocok dengan data dokumen saat ini.';
        }

        if ($fileResult && ! $fileResult['matched']) {
            return 'Kode ditemukan, tetapi file upload tidak sama dengan file final yang ditandatangani.';
        }

        if ($fileResult) {
            return 'Kode, tanda tangan digital, dan file upload cocok dengan arsip final.';
        }

        return 'Kode ditemukan dan tanda tangan digital masih cocok dengan arsip sistem.';
    }
}
