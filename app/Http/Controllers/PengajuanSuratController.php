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
        abort_if(Auth::user()->role === 'admin', 403);

        $jenisSurats = JenisSurat::aktif()->orderBy('nama')->get();
        $templateDefinitions = $this->templateService->definitions();

        return view('pengajuan-surat.create', compact('jenisSurats', 'templateDefinitions'));
    }

    public function store(Request $request)
    {
        abort_if(Auth::user()->role === 'admin', 403);

        $baseData = $request->validate([
            'jenis_surat_id' => ['required', 'exists:jenis_surats,id'],
            'tanggal_pengajuan' => ['required', 'date'],
            'perihal' => ['required', 'string', 'max:1000'],
        ]);

        $jenisSurat = JenisSurat::findOrFail($baseData['jenis_surat_id']);
        $templateDefinition = $this->templateService->definition($jenisSurat->slug);
        $fieldData = $request->validate($this->templateService->validationRules($jenisSurat->slug));
        $cleanFields = $this->templateService->cleanFields($jenisSurat->slug, $fieldData['fields'] ?? []);
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

        if ($user->role === 'kasi') {
            return User::where('role', 'kabid')->first();
        }

        return null;
    }

    private function generateNomorPengajuan(): string
    {
        $prefix = 'PGJ-'.now()->format('Ymd');
        $countToday = PengajuanSurat::whereDate('created_at', now()->toDateString())->count() + 1;

        return $prefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);
    }
}
