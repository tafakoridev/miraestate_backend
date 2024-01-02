<?php

namespace App\Http\Controllers;

use App\Models\Commodity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class CommodityController extends Controller
{
    public function index()
    {
        $commodities = Commodity::with(['city', 'category', 'agent.agent', 'user'])
        ->where('expired_at', '>', now()) // Retrieve records where expired_at is in the future
        ->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function indexByCity($city_id)
    {
        $commodities = Commodity::where('city_id', $city_id)->with(['city', 'category','agent.agent',])->where('expired_at', '>', now())->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function show($id)
    {
        $commodity = Commodity::with(['city.province', 'category', 'agent.agent', 'user'])->find($id);

        if (!$commodity) {
            return response(['message' => 'Commodity not found.'], Response::HTTP_NOT_FOUND);
        }

        return response(['commodity' => $commodity], Response::HTTP_OK);
    }

  public function update(Request $request, $id)
{
    $user = $request->user();
    $commodity = Commodity::find($id);

    if (!$commodity) {
        return response(['message' => 'Commodity not found.'], Response::HTTP_NOT_FOUND);
    }

    $validatedData = $request->validate([
        'title' => 'sometimes|string|max:255',
        'description' => 'sometimes|string',
        'price' => 'sometimes|numeric',
        'category_id' => 'sometimes|exists:categories,id',
        'city_id' => 'sometimes|exists:cities,id',
        'agent_id' => 'sometimes|exists:users,id',
        'picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $validatedData['user_id'] = $user->id;

    // Update the commodity attributes
    Commodity::where('id', $id)->update($validatedData);
    // Handle file upload if a new picture is provided
    if ($request->hasFile('picture')) {
        // Handle file upload
        $picturePath = $request->file('picture')->store('commodity_pictures', 'public');
   
        
        Commodity::where('id', $id)->update(['picture' => '/storage/' . $picturePath]);
    }
    
    $commodity = Commodity::find($id);
    return response(['commodity' => $commodity], Response::HTTP_OK);
}

    public function destroy($id)
    {
        $commodity = Commodity::find($id);

        if (!$commodity) {
            return response(['message' => 'Commodity not found.'], Response::HTTP_NOT_FOUND);
        }

        // Delete the commodity
        $commodity->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if($request->agent_id)
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'agent_id' => 'exists:users,id',
            'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        else $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'category_id' => 'required|exists:categories,id',
            'city_id' => 'required|exists:cities,id',
            'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $validatedData['user_id'] = $user->id;
        // Handle file upload (you may want to customize this based on your file storage setup)
        if($validatedData['picture'])
        $picturePath = $request->file('picture')->store('commodity_pictures', 'public');

        $commodity = Commodity::create([
            'user_id' => $validatedData['user_id'],
            'category_id' => $validatedData['category_id'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'city_id' => $validatedData['city_id'],
            'agent_id' => $validatedData['agent_id'] ?? null,
            'picture' => $validatedData['picture'] ? '/storage/' . $picturePath : "",
            'expired_at' => Carbon::now()->addDays(30)
        ]);

        return response(['commodity' => $commodity], Response::HTTP_CREATED);
    }
}
