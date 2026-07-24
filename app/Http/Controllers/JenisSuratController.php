<?php

namespace App\Http\Controllers;

use App\Models\JenisSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class JenisSuratController extends Controller
{
    public function index()
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $jenisSurats = JenisSurat::withCount('pengajuanSurats')
            ->orderBy('nama')
            ->get();

        return view('jenis-surat.index', compact('jenisSurats'));
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['nama']);
        $data['is_active'] = $request->boolean('is_active', true);

        JenisSurat::create($data);

        return back()->with('success', 'Jenis surat berhasil ditambahkan.');
    }

    public function update(Request $request, JenisSurat $jenisSurat)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = Str::slug($data['nama']);
        $data['is_active'] = $request->boolean('is_active');

        $jenisSurat->update($data);

        return back()->with('success', 'Jenis surat berhasil diperbarui.');
    }

    public function destroy(JenisSurat $jenisSurat)
    {
        abort_unless(auth()->user()->role === 'admin', 403);

        if ($jenisSurat->pengajuanSurats()->exists()) {
            return back()->with('error', 'Jenis surat sudah dipakai pengajuan dan tidak bisa dihapus.');
        }

        $jenisSurat->delete();

        return back()->with('success', 'Jenis surat berhasil dihapus.');
    }
}
