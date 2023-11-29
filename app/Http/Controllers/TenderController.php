<?php

namespace App\Http\Controllers;

use App\Models\Purpose;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tender;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = Tender::with(['agent.agent', 'user', 'department', 'purpose.user'])->get();
        return response(['tenders' => $tenders], Response::HTTP_OK);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // This is an API, so you might not need a specific method for creating.
        // Instead, you can handle creation in the store method.
    }

    public function Purpose(Request $request)
    {
        $user = $request->user();
        $id = $request->id;
        $tender = Tender::find($id);
        $purpose = new Purpose(['description' => $request->description, 'user_id' => $user->id]);
        $tender->purpose()->save($purpose);
        return true;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'exists:users,id',
            'department_id' => 'exists:departments,id',
            'agent_id' => 'exists:users,id',
            'title' => 'string|max:255',
            'description' => 'string',
        ]);

        $tender = Tender::create($validatedData);

        return response(['tender' => $tender], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tender = Tender::with(['agent.agent', 'user', 'department', 'purpose.user'])->findOrFail($id);
        return response(['tender' => $tender], Response::HTTP_OK);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // This is an API, so you might not need a specific method for editing.
        // Instead, you can handle editing in the update method.
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'agent_id' => 'exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ]);

        $tender = Tender::findOrFail($id);
        $tender->update($validatedData);

        return response(['tender' => $tender], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tender = Tender::findOrFail($id);
        $tender->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
