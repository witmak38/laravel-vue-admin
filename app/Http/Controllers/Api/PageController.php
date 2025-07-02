<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Page;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return Page::latest()->paginate($request->get('per_page', 10));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        // Валидация входящих данных
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:255', 'unique:pages,slug'],
            'name' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            //'meta' => ['nullable', 'array'],           // динамические поля (key-value)
            //'meta.*.key' => ['required_with:meta', 'string'],
            //'meta.*.value' => ['nullable', 'string'],
        ]);

        // Создаем страницу с основными полями
        $page = Page::create([
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'content' => $validated['content'] ?? null,
            'meta_title' => $validated['meta_title'] ?? null,
            'meta_description' => $validated['meta_description'] ?? null,
        ]);

        // Если есть файл картинки, сохраняем её
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('public/images/upload/pages');

            // Создаем запись в images с полиморфной связью
            $page->images()->create([
                'path' => Storage::url($path),
                'alt' => $validated['image_alt'] ?? $page->title,
                'title' => $validated['image_title'] ?? null,
            ]);
        }

        // Если есть динамические мета-поля — сохраняем их
        if (!empty($validated['meta'])) {
            foreach ($validated['meta'] as $metaItem) {
                $page->meta()->create([
                    'key' => $metaItem['key'],
                    'value' => $metaItem['value'] ?? null,
                ]);
            }
        }
//        заготовка на будущее
//        return response()->json([
//            'message' => 'Page created',
//            'page' => $page->load('meta'),
//        ], 201);
        return response()->json([
            'message' => 'Page created',
            'page' => $page->load(['images']),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $page = Page::find($id);

        if (!$page) {
            return response()->json(['message' => 'Page not found'], 404);
        }

        $page->delete(); // это удалит и мета-данные, если настроена каскадная связь

       // return response()->json(['message' => 'Page deleted'], 200);
        return response()->json(['message' => 'Deleted'], 200);
    }
}
