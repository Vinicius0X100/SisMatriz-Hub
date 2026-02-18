<?php

namespace App\Http\Controllers;

use App\Models\Bucket;
use App\Models\BucketFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BucketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Bucket::where('paroquia_id', $user->paroquia_id)
            ->where('user_id', $user->id);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', '%' . $search . '%');
        }

        $buckets = $query->orderBy('created_at', 'desc')->paginate(12);

        $totalUsed = (int) Bucket::where('paroquia_id', $user->paroquia_id)
            ->where('user_id', $user->id)
            ->sum('tamanho');

        return view('modules.buckets.index', compact('buckets', 'totalUsed'));
    }

    public function create()
    {
        return view('modules.buckets.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $user = Auth::user();

        $rand = $this->generateUniqueRand();

        $bucket = Bucket::create([
            'rand' => $rand,
            'user_id' => $user->id,
            'paroquia_id' => $user->paroquia_id,
            'name' => $request->input('name'),
            'tamanho' => 0,
            'regiao' => 1,
            'tamanho_max' => 1024 * 1024 * 1024,
            'created_at' => now(),
        ]);

        $relativePath = 'uploads/buckets/' . $bucket->user_id . '/' . $bucket->rand;
        Storage::disk('public')->makeDirectory($relativePath);

        session()->flash('bucket_notification', [
            'title' => 'Bucket criado com sucesso',
            'message' => 'O bucket "' . $bucket->name . '" foi criado. Ele possui limite de 1 GB e pode armazenar imagens, vídeos, documentos, PDFs e outros arquivos para a sua paróquia.',
            'created_at' => now(),
        ]);

        return redirect()->route('buckets.show', $bucket)->with('success', 'Bucket criado com sucesso.');
    }

    public function show(Bucket $bucket)
    {
        $this->ensureBucketAccess($bucket);

        $files = $bucket->files()->orderByDesc('upload_date')->paginate(20);

        $used = (int) $bucket->tamanho;
        $max = (int) $bucket->tamanho_max;
        $percent = $max > 0 ? min(100, round($used / $max * 100)) : 0;

        return view('modules.buckets.show', compact('bucket', 'files', 'used', 'max', 'percent'));
    }

    public function storeFile(Request $request, Bucket $bucket)
    {
        $this->ensureBucketAccess($bucket);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['file', 'max:51200'],
        ]);

        $files = $request->file('files', []);

        $totalNewSize = 0;
        foreach ($files as $file) {
            $totalNewSize += $file->getSize();
        }

        if ($bucket->tamanho + $totalNewSize > $bucket->tamanho_max) {
            return back()->with('error', 'Esse bucket atingiu o limite de 1 GB. Remova alguns arquivos antes de enviar novos.');
        }

        $baseDir = 'uploads/buckets/' . $bucket->user_id . '/' . $bucket->rand;
        Storage::disk('public')->makeDirectory($baseDir);

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $size = $file->getSize();

            $safeName = $this->sanitizeFileName($originalName);
            $path = Storage::disk('public')->putFileAs($baseDir, $file, $safeName);

            BucketFile::create([
                'bucket_id' => $bucket->id,
                'bucket_rand' => $bucket->rand,
                'file_name' => $originalName,
                'file_path' => $path,
                'file_size' => $size,
                'upload_date' => now(),
            ]);

            $bucket->tamanho += $size;
        }

        $bucket->save();

        return back()->with('success', 'Arquivos enviados com sucesso.');
    }

    public function destroy(Bucket $bucket)
    {
        $this->ensureBucketAccess($bucket);

        $files = $bucket->files;
        $baseDir = 'uploads/buckets/' . $bucket->user_id . '/' . $bucket->rand;

        foreach ($files as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        if (Storage::disk('public')->exists($baseDir)) {
            Storage::disk('public')->deleteDirectory($baseDir);
        }

        $bucket->delete();

        return redirect()->route('buckets.index')->with('success', 'Bucket e todos os arquivos foram destruídos permanentemente.');
    }

    public function destroyFile(BucketFile $file)
    {
        $bucket = $file->bucket;
        $this->ensureBucketAccess($bucket);

        if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        if ($bucket->tamanho && $file->file_size) {
            $bucket->tamanho = max(0, $bucket->tamanho - $file->file_size);
            $bucket->save();
        }

        $file->delete();

        return back()->with('success', 'Arquivo removido com sucesso.');
    }

    public function bulkDestroyFiles(Request $request, Bucket $bucket)
    {
        $this->ensureBucketAccess($bucket);

        $request->validate([
            'files' => ['required', 'array'],
            'files.*' => ['integer'],
        ]);

        $ids = $request->input('files', []);

        $files = BucketFile::where('bucket_id', $bucket->id)
            ->whereIn('id', $ids)
            ->get();

        if ($files->isEmpty()) {
            return back()->with('error', 'Nenhum arquivo válido foi selecionado.');
        }

        $totalSize = 0;

        foreach ($files as $file) {
            if ($file->file_path && Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            if ($file->file_size) {
                $totalSize += $file->file_size;
            }

            $file->delete();
        }

        if ($bucket->tamanho && $totalSize > 0) {
            $bucket->tamanho = max(0, $bucket->tamanho - $totalSize);
            $bucket->save();
        }

        return back()->with('success', 'Arquivos selecionados foram removidos com sucesso.');
    }

    protected function ensureBucketAccess(Bucket $bucket): void
    {
        $user = Auth::user();
        if ($bucket->user_id !== $user->id || $bucket->paroquia_id !== $user->paroquia_id) {
            abort(403);
        }
    }

    protected function generateUniqueRand(): int
    {
        do {
            $rand = random_int(100000, 999999);
        } while (Bucket::where('rand', $rand)->exists());

        return $rand;
    }

    protected function sanitizeFileName(string $name): string
    {
        $name = str_replace(['\\', '/'], '-', $name);
        return trim($name);
    }
}
