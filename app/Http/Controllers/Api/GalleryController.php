<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Gallery;


class GalleryController extends Controller
{
    public function index(Request $request)
    {
        return Gallery::latest()->paginate($request->get('per_page', 10));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // до 5МБ
        ]);

        $path = $request->file('image')->store('images/gallery', 'public');

        $photo = Gallery::create([
            'path' => $path,
        ]);

        return response()->json($photo, 201);
    }

    public function destroy(Gallery $gallery)
    {

        // Удаление файла, если нужно
        if ($gallery->path) {
            Storage::disk('public')->delete($gallery->path);
        }

        $gallery->delete();

        return response()->json(['message' => 'Deleted'], 200);
    }
}
