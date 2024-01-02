<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Auction; // Make sure to use the correct namespace for your Auction model
use App\Models\Purpose;
use Illuminate\Http\Response;
use Illuminate\Notifications\Action;

class AuctionController extends Controller
{
  // ... (existing methods)

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $auctions = Auction::with(['agent.agent', 'user', 'department', 'purpose.user'])->get();
        return response(['auctions' => $auctions], Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'agent_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $auction = Auction::create($validatedData);

        return response(['auction' => $auction], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auction = Auction::with(['agent.agent', 'user', 'department', 'purpose.user'])->findOrFail($id);
        return response(['auction' => $auction], Response::HTTP_OK);
    }

    public function Purpose(Request $request)
    {
        $user = $request->user();
        $id = $request->id;
        $auction = Auction::find($id);
        $purpose = new Purpose(['description' => $request->description, 'user_id' => $user->id]);
        $auction->purpose()->save($purpose);
        return true;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        if($request->agent_id)
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
            'agent_id' => 'required|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]); else $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'department_id' => 'sometimes|exists:departments,id',
          
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $auction = Auction::findOrFail($id);
        $auction->update($validatedData);

        return response(['auction' => $auction], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $auction = Auction::findOrFail($id);
        $auction->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
