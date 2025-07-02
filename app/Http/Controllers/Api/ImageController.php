<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    // Загрузка изображения и привязка к модели
    public function store(Request $request)
    {
        $request->validate([
            'imageable_type' => 'required|string',
            'imageable_id' => 'required|integer',
            'image' => 'required|image|max:5120', // макс 5MB
            'alt' => 'nullable|string|max:255',
            'title' => 'nullable|string|max:255',
        ]);

        // Загружаем файл в хранилище (например, storage/app/public/images/upload)
        $path = $request->file('image')->store('public/images/upload');

        $image = Image::create([
            'imageable_type' => $request->imageable_type,
            'imageable_id' => $request->imageable_id,
            'path' => Storage::url($path),
            'alt' => $request->alt,
            'title' => $request->title,
            'sort_order' => 0,
        ]);

        return response()->json(['message' => 'Image uploaded', 'image' => $image], 201);
    }
    // Удаление изображения
    public function destroy($id)
    {
        $image = Image::find($id);
        if (!$image) {
            return response()->json(['message' => 'Image not found'], 404);
        }

        // Удалить файл из storage
        $filePath = str_replace('/storage/', 'public/', $image->path);
        Storage::delete($filePath);

        $image->delete();

        return response()->json(['message' => 'Image deleted'], 200);
    }

}
