<?php

namespace App\Http\Controllers;

use App\Http\Resources\FileUploadResource;
use App\Http\Requests\FileUploadRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Jobs\FileUploadJob;
use App\Models\FileUpload;

class FileUploadController extends Controller
{
    public function index()
    {
        return view('file-upload.index');
    }

    public function store(FileUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $originalFilename = $file->getClientOriginalName();
        
        $filename = $file->store('uploads');

        $fileUpload = FileUpload::create([
            'filename' => $filename,
            'original_filename' => $originalFilename,
            'status' => 'pending',
        ]);

        FileUploadJob::dispatch($fileUpload);

        return response()->json([
            'success' => true,
            'message' => 'File uploaded successfully. Processing started.',
            'data' => new FileUploadResource($fileUpload),
        ], 201);
    }

    public function list(): JsonResponse
    {
        $uploads = FileUpload::orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'success' => true,
            'data' => FileUploadResource::collection($uploads),
        ]);
    }

    public function status(FileUpload $fileUpload): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => new FileUploadResource($fileUpload),
        ]);
    }
}
