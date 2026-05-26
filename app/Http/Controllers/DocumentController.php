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
            ->latest()
            ->paginate(20);

        return Inertia::render('Documents/Index', [
            'documents' => $documents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:51200'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'documentable_type' => ['nullable', 'string'],
            'documentable_id' => ['nullable', 'integer'],
        ]);

        $file = $request->file('file');
        $path = $file->store('documents', 'local');

        Document::create([
            'project_id' => $request->project_id,
            'uploaded_by' => $request->user()->id,
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

    public function destroy(Document $document)
    {
        Storage::disk('local')->delete($document->storage_path);
        $document->delete();

        return back()->with('success', __('documents.deleted'));
    }
}
