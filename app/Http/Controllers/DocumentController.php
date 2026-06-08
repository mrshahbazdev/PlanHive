<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $documents = Document::where('uploaded_by', $request->user()->id)
            ->with('project:id,name,color')
            ->when($request->project_id, fn ($q) => $q->where('project_id', $request->project_id))
            ->when($request->folder, fn ($q) => $q->where('folder', $request->folder))
            ->when($request->search, fn ($q, $search) =>
                $q->where('original_name', 'like', "%{$search}%")
            )
            ->latest()
            ->paginate(20);

        $folders = Document::where('uploaded_by', $request->user()->id)
            ->whereNotNull('folder')
            ->distinct()
            ->pluck('folder');

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
            'folders' => $folders,
            'filters' => $request->only(['search', 'folder', 'project_id']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'folder' => ['nullable', 'string', 'max:255'],
            'documentable_type' => ['nullable', 'string'],
            'documentable_id' => ['nullable', 'integer'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        Document::create([
            'project_id' => $request->project_id,
            'uploaded_by' => $request->user()->id,
            'folder' => $request->folder,
            'documentable_type' => $request->documentable_type,
            'documentable_id' => $request->documentable_id,
            'original_name' => $file->getClientOriginalName(),
            'storage_path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
        ]);

        return back()->with('success', __('documents.uploaded'));
    }

    public function download(Document $document)
    {
        return Storage::disk('local')->download(
            $document->storage_path,
            $document->original_name
        );
    }

    public function preview(Document $document)
    {
        $previewable = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'application/pdf'];

        if (!in_array($document->mime_type, $previewable)) {
            return $this->download($document);
        }

        return Storage::disk('local')->response(
            $document->storage_path,
            $document->original_name,
            ['Content-Type' => $document->mime_type]
        );
    }

    public function destroy(Document $document)
    {
        Storage::disk('local')->delete($document->storage_path);
        $document->delete();

        return back()->with('success', __('documents.deleted'));
    }
}
