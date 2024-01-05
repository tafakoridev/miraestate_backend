<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Category;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::get();
        return response(['categories' => $categories], Response::HTTP_OK);
    }

    public function StepIndex()
    {
        $categories = Category::with('recursiveChildren')->whereNull('parent_id')->get();

        return response(['categories' => $categories], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'parent_id' => 'string|max:255',
            'price' => 'string|max:255',
        ]);

        $category = Category::create($validatedData);

        return response(['category' => $category], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $category = Category::findOrFail($id);
        return response(['category' => $category], Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'parent_id' => 'max:255',
            'price' => 'string|max:255',
        ]);


        $category = Category::findOrFail($id);
        $category->update($validatedData);

        return response(['category' => $category], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
