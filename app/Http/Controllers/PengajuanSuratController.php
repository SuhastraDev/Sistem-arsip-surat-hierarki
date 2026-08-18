<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use App\Models\PengajuanSurat;
use App\Models\RiwayatPengajuanSurat;
use App\Models\User;
use App\Services\DigitalSignatureService;
use App\Services\PengajuanApprovalService;
use App\Services\SuratTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PengajuanSuratController extends Controller
{
    public function __construct(
        private readonly SuratTemplateService $templateService,
        private readonly DigitalSignatureService $digitalSignatureService,
        private readonly PengajuanApprovalService $approvalService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $query = PengajuanSurat::with(['jenisSurat', 'pemohon', 'posisi']);

        if ($request->filled('search')) {
            $query->where('perihal', 'like', '%'.$request->search.'%');
        }

        if ($request->filled('jenis_surat_id')) {
            $query->where('jenis_surat_id', $request->jenis_surat_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_pengajuan', $request->tahun);
        }

        if ($user->role === 'staff') {
            $query->where('pemohon_id', $user->id);
        } elseif (in_array($user->role, ['kasi', 'kabid'])) {
            $query->where(function ($subQuery) use ($user): void {
                $subQuery->where('posisi_saat_ini', $user->id)
                    ->orWhere('pemohon_id', $user->id)
                    ->orWhereHas('riwayat', function ($historyQuery) use ($user): void {
                        $historyQuery->where('actor_id', $user->id);
                    });
            });
        }

        $pengajuanSurats = $query->latest()->paginate(15);
        $jenisSurats = JenisSurat::orderBy('nama')->get();
        $statusOptions = PengajuanSurat::STATUS;

        return view('pengajuan-surat.index', compact('pengajuanSurats', 'jenisSurats', 'statusOptions'));
    }

    public function create()
    {
        abort_unless(Auth::user()->role === 'staff', 403, 'Pengajuan surat hanya dibuat oleh Staff.');

        $jenisSurats = JenisSurat::aktif()->orderBy('nama')->get();
        $templateDefinitions = $this->templateService->definitions();
        $kabidProfile = $this->kabidProfile();
        $pegawaiProfile = [
            'name' => Auth::user()->name,
            'nip' => Auth::user()->nip ?: '-',
            'jabatan' => Auth::user()->jabatan ?: '-',
            'atasan_langsung' => $this->atasanLangsung(),
            'kabid_penandatangan' => $kabidProfile['summary'],
            'kabid_nama' => $kabidProfile['name'],
            'kabid_jabatan' => $kabidProfile['jabatan'],
            'kabid_nip' => $kabidProfile['nip'],
            'kabid_pangkat' => $kabidProfile['pangkat'],
            'surat_tugas_nomor' => $this->generateNomorSuratTugas(),
            'surat_tugas_dasar_pertama' => $this->suratTugasDasarPertama(),
            'surat_tugas_dasar_kedua' => $this->suratTugasDasarKedua(),
        ];
        $cutiUsage = [
            'year' => (int) now()->format('Y'),
            'types' => $this->leaveQuotaDefinitions(),
            'usage' => $this->leaveUsageByTypeAndYear(Auth::id()),
        ];

        return view('pengajuan-surat.create', compact('jenisSurats', 'templateDefinitions', 'pegawaiProfile', 'cutiUsage'));
    }

    public function store(Request $request)
    {
        abort_unless(Auth::user()->role === 'staff', 403, 'Pengajuan surat hanya dibuat oleh Staff.');

        $baseData = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
            'tanggal_pengajuan' => ['required', 'date', 'after_or_equal:today'],
            'perihal' => ['required', 'string', 'max:1000'],
        ]);

        $jenisSurat = JenisSurat::findOrFail($baseData['jenis_surat_id']);
        $templateDefinition = $this->templateService->definition($jenisSurat->slug);
        $fieldData = $request->validate($this->templateService->validationRules($jenisSurat->slug));
        $cleanFields = $this->templateService->cleanFields($jenisSurat->slug, $fieldData['fields'] ?? []);
        $cleanFields = $this->hydrateSystemFields($jenisSurat->slug, $cleanFields);
        $cleanFields = $this->hydrateNotaDinasFields($jenisSurat->slug, $cleanFields);
        $cleanFields = $this->hydrateSuratTugasFields($jenisSurat->slug, $cleanFields);
        $cleanFields = $this->attachUploadedFiles($request, $jenisSurat->slug, $cleanFields);
        $quotaSummary = $this->validateCutiQuota($jenisSurat->slug, $cleanFields);
        $posisiAwal = $this->resolvePosisiAwal();

        if (! $posisiAwal) {
            return back()
                ->withInput()
                ->with('error', 'Akun Anda belum memiliki jalur atasan untuk pengajuan. Hubungi Admin.');
        }

        $pengajuanSurat = PengajuanSurat::create([
            'jenis_surat_id' => $baseData['jenis_surat_id'],
            'pemohon_id' => Auth::id(),
            'nomor_pengajuan' => $this->generateNomorPengajuan(),
            'tanggal_pengajuan' => $baseData['tanggal_pengajuan'],
            'perihal' => $baseData['perihal'],
            'status' => 'diajukan',
            'posisi_saat_ini' => $posisiAwal->id,
            'metadata' => [
                'fase' => 'fase_2',
                'form_data' => $cleanFields,
                'cuti_quota' => $quotaSummary,
                'template_source' => $templateDefinition['template_label'] ?? 'Template sistem',
                'template_format' => ['pdf', 'docx'],
                'catatan' => 'Form persyaratan mengikuti template resmi yang disediakan. Output unduhan disediakan dalam PDF dan DOCX.',
            ],
        ]);

        RiwayatPengajuanSurat::create([
            'pengajuan_surat_id' => $pengajuanSurat->id,
            'actor_id' => Auth::id(),
            'target_user_id' => $posisiAwal->id,
            'aksi' => 'diajukan',
            'status_sebelum' => null,
            'status_sesudah' => 'diajukan',
            'catatan' => 'Pengajuan dibuat dan dikirim ke pemeriksaan awal.',
            'metadata' => ['actor_role' => Auth::user()->role],
        ]);

        return redirect()
            ->route('pengajuan-surat.index')
            ->with('success', 'Pengajuan surat berhasil dibuat dan masuk ke antrean pemeriksaan.');
    }

    public function show(PengajuanSurat $pengajuanSurat)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'posisi', 'digitalSignature.signer', 'riwayat.actor', 'riwayat.targetUser']);
        $user = Auth::user();

        $isAllowed = $user->role === 'admin'
            || $pengajuanSurat->pemohon_id === $user->id
            || $pengajuanSurat->posisi_saat_ini === $user->id
            || $pengajuanSurat->digitalSignature?->signer_id === $user->id
            || $pengajuanSurat->riwayat()->where('actor_id', $user->id)->exists();

        abort_unless($isAllowed, 403);

        $templateRows = $this->templateService->templateRows($pengajuanSurat);
        $templateDefinition = $this->templateService->definition($pengajuanSurat->jenisSurat->slug);

        return view('pengajuan-surat.show', compact('pengajuanSurat', 'templateRows', 'templateDefinition'));
    }

    public function destroy(PengajuanSurat $pengajuanSurat)
    {
        abort_unless($this->canDeleteOwnDraftOrSubmittedPengajuan($pengajuanSurat), 403, 'Pengajuan ini sudah masuk pemeriksaan dan tidak bisa dihapus.');

        $this->deleteUploadedAttachments($pengajuanSurat);
        $pengajuanSurat->delete();

        return redirect()
            ->route('pengajuan-surat.index')
            ->with('success', 'Pengajuan surat berhasil dihapus.');
    }

    public function sign(PengajuanSurat $pengajuanSurat)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'digitalSignature']);

        try {
            $this->digitalSignatureService->sign($pengajuanSurat, Auth::user());
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Dokumen berhasil ditandatangani. Barcode/QR ditempatkan di area TTD, PDF final membawa barcode verifikasi, dan hasil final dikirim kembali ke Staff pemohon.');
    }

    public function process(Request $request, PengajuanSurat $pengajuanSurat)
    {
        $data = $request->validate([
            'aksi' => ['required', 'in:periksa,acc,revisi,ditolak,ajukan_ulang'],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ]);

        $pengajuanSurat->load(['pemohon', 'posisi']);

        try {
            $this->approvalService->process($pengajuanSurat, Auth::user(), $data['aksi'], $data['catatan'] ?? null);
        } catch (RuntimeException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Aksi pengajuan berhasil diproses.');
    }

    public function preview(PengajuanSurat $pengajuanSurat)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'posisi', 'digitalSignature.signer']);
        $this->authorizeView($pengajuanSurat);

        $pdfPreview = $this->templateService->previewPdf($pengajuanSurat);

        if ($pdfPreview) {
            return $pdfPreview;
        }

        return view('pengajuan-surat.template', [
            'pengajuanSurat' => $pengajuanSurat,
            'rows' => $this->templateService->templateRows($pengajuanSurat),
            'templateDefinition' => $this->templateService->definition($pengajuanSurat->jenisSurat->slug),
            'isEmbed' => request()->boolean('embed'),
            'isPrint' => false,
        ]);
    }

    public function export(PengajuanSurat $pengajuanSurat, string $format)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'posisi', 'digitalSignature.signer']);
        $this->authorizeView($pengajuanSurat);

        return match ($format) {
            'pdf' => $this->templateService->downloadPdf($pengajuanSurat),
            'docx' => $this->templateService->downloadDocx($pengajuanSurat),
            default => abort(404),
        };
    }

    public function attachment(PengajuanSurat $pengajuanSurat, string $field)
    {
        $pengajuanSurat->load(['jenisSurat', 'pemohon', 'posisi', 'digitalSignature.signer']);
        $this->authorizeView($pengajuanSurat);

        $attachment = $pengajuanSurat->metadata['form_data'][$field] ?? null;
        abort_unless(is_array($attachment) && isset($attachment['path']), 404);
        abort_unless(Storage::disk('local')->exists($attachment['path']), 404);

        return Storage::disk('local')->download($attachment['path'], $attachment['original_name'] ?? null);
    }

    private function authorizeView(PengajuanSurat $pengajuanSurat): void
    {
        $user = Auth::user();
        $isAllowed = $user->role === 'admin'
            || $pengajuanSurat->pemohon_id === $user->id
            || $pengajuanSurat->posisi_saat_ini === $user->id
            || $pengajuanSurat->digitalSignature?->signer_id === $user->id
            || $pengajuanSurat->riwayat()->where('actor_id', $user->id)->exists();

        abort_unless($isAllowed, 403);
    }

    private function resolvePosisiAwal(): ?User
    {
        $user = Auth::user();

        if ($user->role === 'staff' && $user->parent_id) {
            return User::find($user->parent_id);
        }

        return null;
    }

    private function hydrateSystemFields(string $slug, array $fields): array
    {
        if ($slug !== 'surat-cuti') {
            return $fields;
        }

        $user = Auth::user();
        $fields['nama_pegawai'] = $user->name;
        $fields['nip'] = $user->nip ?: '-';
        $fields['jabatan_unit'] = $user->jabatan ?: '-';
        $fields['atasan_langsung'] = $this->atasanLangsung();

        if (! empty($fields['tanggal_mulai']) && ! empty($fields['tanggal_selesai'])) {
            $fields['lama_cuti'] = $this->calculateLeaveDays($fields['tanggal_mulai'], $fields['tanggal_selesai']).' hari';
        }

        return $fields;
    }

    private function hydrateNotaDinasFields(string $slug, array $fields): array
    {
        if ($slug !== 'nota-dinas') {
            return $fields;
        }

        $fields['kepada'] = 'Kepala Dinas Kehutanan Provinsi Sumatera Selatan';
        $fields['nomor_nota'] = $this->generateNomorNotaDinas();
        $kabidProfile = $this->kabidProfile();
        $fields['penandatangan'] = $kabidProfile['summary'];
        $fields['nama_penandatangan'] = $kabidProfile['name'];
        $fields['jabatan_penandatangan'] = $kabidProfile['jabatan'];
        $fields['nip_penandatangan'] = $kabidProfile['nip'];
        $fields['pangkat_penandatangan'] = $kabidProfile['pangkat'];
        $fields['dari'] = $kabidProfile['jabatan'];

        $fields['bulan_laporan'] = $fields['bulan_laporan'] ?? $this->monthNameFromDate($fields['tanggal_nota'] ?? now()->toDateString());
        $fields['tahun_laporan'] = $fields['tahun_laporan'] ?? date('Y', strtotime($fields['tanggal_nota'] ?? now()->toDateString()));
        $fields['bidang_pelapor'] = $kabidProfile['jabatan'];
        $fields['lampiran_text'] = $fields['lampiran_text'] ?? '1 (satu) berkas';

        foreach ($this->templateService->fields($slug) as $key => $field) {
            if (! array_key_exists('default', $field)) {
                continue;
            }

            if (! isset($fields[$key]) || trim((string) $fields[$key]) === '') {
                $fields[$key] = $field['default'];
            }
        }

        return $fields;
    }

    private function hydrateSuratTugasFields(string $slug, array $fields): array
    {
        if ($slug !== 'surat-tugas') {
            return $fields;
        }

        $start = $fields['tanggal_mulai_perjalanan'] ?? null;
        $end = $fields['tanggal_selesai_perjalanan'] ?? null;
        $days = $this->calculateLeaveDays($start, $end);

        if ($days < 1) {
            throw ValidationException::withMessages([
                'fields.tanggal_selesai_perjalanan' => 'Tanggal selesai perjalanan harus sama atau setelah tanggal mulai.',
            ]);
        }

        $fields['nomor_surat'] = $this->generateNomorSuratTugas();
        $fields['dasar_pertama'] = $this->suratTugasDasarPertama();
        $fields['dasar_kedua'] = $this->suratTugasDasarKedua();
        $fields['lama_perjalanan'] = $days.' hari / '.$this->formatIndonesianDate($start).' s.d. '.$this->formatIndonesianDate($end);
        $fields['penandatangan'] = $this->kabidPenandatangan();

        return $fields;
    }

    private function suratTugasDasarPertama(): string
    {
        return 'Peraturan Gubernur Sumatera Selatan Nomor: 48 Tahun 2016 tentang Susunan Organisasi, Uraian Tugas dan Fungsi Dinas Kehutanan Provinsi Sumatera Selatan.';
    }

    private function suratTugasDasarKedua(): string
    {
        return 'Surat Kepala Badan Perencanaan Pembangunan Daerah Nomor : 000.1.5/1517/Bappeda-IV/2026 Tanggal 21 Juli 2026 tentang Peningkatan Kapasitas dalam Rangka Pembangunan Rendah Karbon Daerah di Provinsi Sumatera Selatan.';
    }

    private function atasanLangsung(): string
    {
        $user = Auth::user();
        $atasan = $user->parent_id ? User::find($user->parent_id) : null;

        if (! $atasan) {
            return 'Kasi belum diatur';
        }

        return trim($atasan->name.' - '.($atasan->jabatan ?: 'Kasi'));
    }

    private function kabidPenandatangan(): string
    {
        return $this->kabidProfile()['summary'];
    }

    private function kabidProfile(): array
    {
        $kabid = User::where('role', 'kabid')->first();

        if (! $kabid) {
            return [
                'name' => 'Kabid belum diatur',
                'jabatan' => 'Kepala Bidang',
                'nip' => '-',
                'pangkat' => '-',
                'summary' => 'Kabid belum diatur',
            ];
        }

        $jabatan = $kabid->jabatan ?: 'Kepala Bidang';
        $nip = $kabid->nip ?: '-';

        return [
            'name' => $kabid->name,
            'jabatan' => $jabatan,
            'nip' => $nip,
            'pangkat' => '-',
            'summary' => trim($kabid->name.' - '.$jabatan),
        ];
    }

    private function monthNameFromDate(?string $date): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $timestamp = strtotime($date ?: now()->toDateString());

        return $months[(int) date('n', $timestamp)];
    }

    private function attachUploadedFiles(Request $request, string $slug, array $fields): array
    {
        foreach ($this->templateService->fields($slug) as $key => $field) {
            if (($field['type'] ?? null) !== 'file' || ! $request->hasFile('fields.'.$key)) {
                continue;
            }

            $file = $request->file('fields.'.$key);
            $path = $file->store('pengajuan-lampiran/'.now()->format('Y/m'), 'local');
            $fields[$key] = [
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }

        return $fields;
    }

    private function validateCutiQuota(string $slug, array $fields): ?array
    {
        if ($slug !== 'surat-cuti') {
            return null;
        }

        $leaveType = $fields['jenis_cuti'] ?? null;
        $start = $fields['tanggal_mulai'] ?? null;
        $end = $fields['tanggal_selesai'] ?? null;

        if (! $start || ! $end) {
            return null;
        }

        $requestedDays = $this->calculateLeaveDays($start, $end);

        if ($requestedDays < 1) {
            throw ValidationException::withMessages([
                'fields.tanggal_selesai' => 'Tanggal selesai cuti harus sama atau setelah tanggal mulai.',
            ]);
        }

        $definition = $leaveType ? $this->leaveQuotaDefinition($leaveType, $start) : null;

        if (! $definition || $definition['quota_days'] === null) {
            return [
                'jenis_cuti' => $leaveType,
                'year' => (int) date('Y', strtotime($start)),
                'quota_label' => $definition['label'] ?? 'Tidak mengurangi kuota',
                'quota_days' => null,
                'used_days_before_request' => 0,
                'requested_days' => $requestedDays,
                'remaining_days_after_request' => null,
                'reduces_quota' => false,
            ];
        }

        $year = (int) date('Y', strtotime($start));
        $usedDays = $this->usedLeaveDays(Auth::id(), $year, $leaveType);
        $remainingDays = $definition['quota_days'] - $usedDays;

        if ($requestedDays > $definition['quota_days']) {
            throw ValidationException::withMessages([
                'fields.tanggal_selesai' => "Pengajuan {$leaveType} maksimal {$definition['label']} dalam satu tahun. Pengajuan ini {$requestedDays} hari.",
            ]);
        }

        if ($requestedDays > $remainingDays) {
            throw ValidationException::withMessages([
                'fields.tanggal_selesai' => "Kuota {$leaveType} tidak mencukupi. Sisa kuota tahun {$year}: {$remainingDays} hari, pengajuan ini {$requestedDays} hari.",
            ]);
        }

        return [
            'jenis_cuti' => $leaveType,
            'year' => $year,
            'quota_label' => $definition['label'],
            'quota_days' => $definition['quota_days'],
            'used_days_before_request' => $usedDays,
            'requested_days' => $requestedDays,
            'remaining_days_after_request' => $remainingDays - $requestedDays,
            'reduces_quota' => true,
        ];
    }

    private function usedLeaveDays(int $userId, int $year, string $leaveType): int
    {
        $jenisCutiId = JenisSurat::where('slug', 'surat-cuti')->value('id');

        if (! $jenisCutiId) {
            return 0;
        }

        return PengajuanSurat::where('pemohon_id', $userId)
            ->where('jenis_surat_id', $jenisCutiId)
            ->whereIn('status', $this->acceptedLeaveStatuses())
            ->get()
            ->sum(function (PengajuanSurat $pengajuan) use ($year, $leaveType): int {
                $data = $pengajuan->metadata['form_data'] ?? [];

                if (($data['jenis_cuti'] ?? null) !== $leaveType) {
                    return 0;
                }

                if ((int) date('Y', strtotime($data['tanggal_mulai'] ?? '')) !== $year) {
                    return 0;
                }

                return $this->calculateLeaveDays($data['tanggal_mulai'] ?? null, $data['tanggal_selesai'] ?? null);
            });
    }

    private function leaveUsageByTypeAndYear(int $userId): array
    {
        $jenisCutiId = JenisSurat::where('slug', 'surat-cuti')->value('id');

        if (! $jenisCutiId) {
            return [];
        }

        return PengajuanSurat::where('pemohon_id', $userId)
            ->where('jenis_surat_id', $jenisCutiId)
            ->whereIn('status', $this->acceptedLeaveStatuses())
            ->get()
            ->reduce(function (array $usage, PengajuanSurat $pengajuan): array {
                $data = $pengajuan->metadata['form_data'] ?? [];
                $leaveType = $data['jenis_cuti'] ?? null;

                if (! $leaveType || empty($data['tanggal_mulai'])) {
                    return $usage;
                }

                $definition = $this->leaveQuotaDefinition($leaveType, $data['tanggal_mulai']);

                if (! $definition || $definition['quota_days'] === null) {
                    return $usage;
                }

                $year = (int) date('Y', strtotime($data['tanggal_mulai']));
                $usage[$leaveType][$year] = ($usage[$leaveType][$year] ?? 0) + $this->calculateLeaveDays($data['tanggal_mulai'] ?? null, $data['tanggal_selesai'] ?? null);

                return $usage;
            }, []);
    }

    private function leaveQuotaDefinitions(?string $start = null): array
    {
        return [
            'Cuti tahunan' => $this->leaveQuotaDefinition('Cuti tahunan', $start),
            'Cuti sakit' => $this->leaveQuotaDefinition('Cuti sakit', $start),
            'Cuti melahirkan' => $this->leaveQuotaDefinition('Cuti melahirkan', $start),
            'Cuti alasan penting' => $this->leaveQuotaDefinition('Cuti alasan penting', $start),
        ];
    }

    private function leaveQuotaDefinition(string $leaveType, ?string $start = null): ?array
    {
        return match ($leaveType) {
            'Cuti tahunan' => ['label' => '12 hari', 'quota_days' => 12, 'unit' => 'hari', 'reduces_quota' => true],
            'Cuti sakit' => ['label' => 'Tidak dihitung', 'quota_days' => null, 'unit' => 'hari', 'reduces_quota' => false],
            'Cuti melahirkan' => ['label' => '3 bulan', 'quota_days' => $this->maternityQuotaDays($start), 'unit' => 'bulan', 'reduces_quota' => true],
            'Cuti alasan penting' => ['label' => '12 hari', 'quota_days' => 12, 'unit' => 'hari', 'reduces_quota' => true],
            default => null,
        };
    }

    private function maternityQuotaDays(?string $start): int
    {
        if (! $start) {
            return 93;
        }

        $startDate = new \DateTimeImmutable($start);
        $endDate = $startDate->modify('+3 months')->modify('-1 day');

        return ((int) $startDate->diff($endDate)->days) + 1;
    }

    private function acceptedLeaveStatuses(): array
    {
        return ['disetujui_kabid', 'selesai'];
    }

    private function canDeleteOwnDraftOrSubmittedPengajuan(PengajuanSurat $pengajuanSurat): bool
    {
        return Auth::user()->role === 'staff'
            && $pengajuanSurat->pemohon_id === Auth::id()
            && in_array($pengajuanSurat->status, ['draft', 'diajukan'], true);
    }

    private function deleteUploadedAttachments(PengajuanSurat $pengajuanSurat): void
    {
        foreach (($pengajuanSurat->metadata['form_data'] ?? []) as $value) {
            if (! is_array($value) || empty($value['path'])) {
                continue;
            }

            Storage::disk('local')->delete($value['path']);
        }
    }

    private function calculateLeaveDays(?string $start, ?string $end): int
    {
        if (! $start || ! $end || strtotime($end) < strtotime($start)) {
            return 0;
        }

        $startDate = new \DateTimeImmutable($start);
        $endDate = new \DateTimeImmutable($end);

        return $startDate->diff($endDate)->days + 1;
    }

    private function generateNomorPengajuan(): string
    {
        $prefix = 'PGJ-'.now()->format('Ymd');
        $countToday = PengajuanSurat::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
    }

    private function generateNomorNotaDinas(): string
    {
        $year = now()->format('Y');
        $countThisYear = PengajuanSurat::whereHas('jenisSurat', fn ($query) => $query->where('slug', 'nota-dinas'))
            ->whereYear('created_at', $year)
            ->count() + 1;

        return '500.0.0.0/'.str_pad((string) $countThisYear, 3, '0', STR_PAD_LEFT).'/ND.DISHUT/I/'.$year;
    }

    private function generateNomorSuratTugas(): string
    {
        $year = now()->format('Y');
        $countThisYear = PengajuanSurat::whereHas('jenisSurat', fn ($query) => $query->where('slug', 'surat-tugas'))
            ->whereYear('created_at', $year)
            ->count() + 1;

        return '800.1.11.1/'.str_pad((string) $countThisYear, 3, '0', STR_PAD_LEFT).'/ST/Dishut.III/'.$year;
    }

    private function formatIndonesianDate(?string $date): string
    {
        if (! $date) {
            return '-';
        }

        return date('d/m/Y', strtotime($date));
    }
}
