<?php

namespace App\Services;

use App\Models\DigitalSignature;
use App\Models\PengajuanSurat;
use App\Models\RiwayatPengajuanSurat;
use App\Models\SignatureKey;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class DigitalSignatureService
{
    public function __construct(private readonly SuratTemplateService $templateService) {}

    public function sign(PengajuanSurat $pengajuanSurat, User $signer): DigitalSignature
    {
        if ($signer->role !== 'kabid') {
            throw new RuntimeException('Hanya Kabid yang dapat menandatangani dokumen.');
        }

        if ($pengajuanSurat->status !== 'disetujui_kabid') {
            throw new RuntimeException('Dokumen harus berstatus Disetujui Kabid sebelum ditandatangani.');
        }

        if ($pengajuanSurat->digitalSignature()->exists()) {
            throw new RuntimeException('Dokumen ini sudah memiliki digital signature.');
        }

        $signatureKey = $this->activeKeyFor($signer);
        $documentPayload = $this->documentPayload($pengajuanSurat);
        $documentHash = hash('sha512', $documentPayload);
        $verificationCode = $this->verificationCode();
        $pdfPath = 'signed-documents/'.$verificationCode.'.pdf';
        $docxPath = 'signed-documents/'.$verificationCode.'.docx';

        $privateKey = openssl_pkey_get_private($signatureKey->encrypted_private_key);

        if (! $privateKey) {
            throw new RuntimeException('Private key Kabid tidak valid.');
        }

        $signature = '';
        $isSigned = openssl_sign($documentPayload, $signature, $privateKey, OPENSSL_ALGO_SHA512);

        if (! $isSigned) {
            throw new RuntimeException('Gagal membuat digital signature.');
        }

        $digitalSignature = DigitalSignature::create([
            'pengajuan_surat_id' => $pengajuanSurat->id,
            'signature_key_id' => $signatureKey->id,
            'signer_id' => $signer->id,
            'document_hash' => $documentHash,
            'signature' => base64_encode($signature),
            'public_key' => $signatureKey->public_key,
            'algorithm' => $signatureKey->algorithm,
            'verification_code' => $verificationCode,
            'signed_at' => now(),
            'metadata' => [
                'payload_version' => 'template_plain_text_v1',
                'hash_algorithm' => 'SHA-512',
                'signature_algorithm' => 'RSA/SHA-512',
            ],
        ]);

        $pengajuanSurat->setRelation('digitalSignature', $digitalSignature->load('signer'));

        $pdfBinary = $this->templateService->pdfBinary($pengajuanSurat);
        $docxBinary = $this->templateService->docxBinary($pengajuanSurat);

        Storage::disk('local')->put($pdfPath, $pdfBinary);
        Storage::disk('local')->put($docxPath, $docxBinary);

        $digitalSignature->update([
            'metadata' => [
                ...($digitalSignature->metadata ?? []),
                'file_hashes' => [
                    'pdf' => hash('sha512', $pdfBinary),
                    'docx' => hash('sha512', $docxBinary),
                ],
                'file_paths' => [
                    'pdf' => $pdfPath,
                    'docx' => $docxPath,
                ],
            ],
        ]);

        $pengajuanSurat->update([
            'status' => 'selesai',
            'posisi_saat_ini' => $pengajuanSurat->pemohon_id,
        ]);

        RiwayatPengajuanSurat::create([
            'pengajuan_surat_id' => $pengajuanSurat->id,
            'actor_id' => $signer->id,
            'target_user_id' => $pengajuanSurat->pemohon_id,
            'aksi' => 'tandatangan_kabid',
            'status_sebelum' => 'disetujui_kabid',
            'status_sesudah' => 'selesai',
            'catatan' => 'Dokumen ditandatangani digital oleh Kabid dan dikirim kembali ke Staff pemohon.',
            'metadata' => [
                'actor_role' => $signer->role,
                'verification_code' => $verificationCode,
            ],
        ]);

        return $digitalSignature;
    }

    public function verify(PengajuanSurat $pengajuanSurat): bool
    {
        $digitalSignature = $pengajuanSurat->digitalSignature;

        if (! $digitalSignature) {
            return false;
        }

        $payload = $this->documentPayload($pengajuanSurat);
        $hash = hash('sha512', $payload);

        if (! hash_equals($digitalSignature->document_hash, $hash)) {
            return false;
        }

        $publicKey = openssl_pkey_get_public($digitalSignature->public_key);

        if (! $publicKey) {
            return false;
        }

        return openssl_verify($payload, base64_decode($digitalSignature->signature), $publicKey, OPENSSL_ALGO_SHA512) === 1;
    }

    private function activeKeyFor(User $signer): SignatureKey
    {
        $existingKey = SignatureKey::where('user_id', $signer->id)
            ->where('is_active', true)
            ->latest()
            ->first();

        if ($existingKey) {
            return $existingKey;
        }

        $config = [
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ];

        if ($opensslConfig = $this->opensslConfigPath()) {
            $config['config'] = $opensslConfig;
        }

        $keyPair = openssl_pkey_new($config);

        if (! $keyPair) {
            throw new RuntimeException('Gagal membuat RSA key pair.');
        }

        $privateKey = '';
        openssl_pkey_export($keyPair, $privateKey, null, $config);
        $details = openssl_pkey_get_details($keyPair);

        if (! isset($details['key'])) {
            throw new RuntimeException('Gagal membaca public key RSA.');
        }

        return SignatureKey::create([
            'user_id' => $signer->id,
            'public_key' => $details['key'],
            'encrypted_private_key' => $privateKey,
            'algorithm' => 'RSA-2048/SHA-512',
            'is_active' => true,
        ]);
    }

    private function documentPayload(PengajuanSurat $pengajuanSurat): string
    {
        $pengajuanSurat->loadMissing(['jenisSurat', 'pemohon']);

        return implode("\n", $this->templateService->canonicalPlainText($pengajuanSurat));
    }

    private function verificationCode(): string
    {
        do {
            $code = 'ES-'.Str::upper(Str::random(10));
        } while (DigitalSignature::where('verification_code', $code)->exists());

        return $code;
    }

    private function opensslConfigPath(): ?string
    {
        $phpDirectory = PHP_BINARY ? dirname(PHP_BINARY) : null;
        $candidates = array_filter([
            getenv('OPENSSL_CONF') ?: null,
            $phpDirectory ? $phpDirectory.DIRECTORY_SEPARATOR.'extras'.DIRECTORY_SEPARATOR.'ssl'.DIRECTORY_SEPARATOR.'openssl.cnf' : null,
            'C:\\laragon\\bin\\apache\\httpd-2.4.62-240904-win64-VS17\\conf\\openssl.cnf',
            '/etc/ssl/openssl.cnf',
        ]);

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
