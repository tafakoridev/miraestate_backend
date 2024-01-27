<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\WalletService;
use Illuminate\Http\Request;
use App\Models\Auction; // Make sure to use the correct namespace for your Auction model
use App\Models\Option;
use App\Models\Purpose;
use Exception;
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
        $auctions = Auction::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser'])->where('is_active', 1)->get();
        return response(['auctions' => $auctions], Response::HTTP_OK);
    }

        /**
     * Display a listing of the resource.
     */
    public function indexUnPublished()
    {
        $auctions = Auction::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser'])->where('is_active', 0)->orderBy('id', 'DESC')->get();
        return response(['auctions' => $auctions], Response::HTTP_OK);
    }
    /**
     * Display a listing of the resource.
     */
    public function indexByUser(Request $request)
    {
        $user = $request->user();
        $auctions = Auction::where('user_id', $user->id)->with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser', 'winner'])->orderBy('id', 'DESC')->get();
        return response(['auctions' => $auctions], Response::HTTP_OK);
    }

    public function Purpose(Request $request)
    {
        $user = $request->user();
        $id = $request->id;
        $auction = Auction::find($id);
        $purpose = new Purpose(['description' => $request->description, 'user_id' => $user->id, 'price' => $request->price]);
        $auction->purpose()->save($purpose);
        return true;
    }

    public function PurposeAccept(Request $request)
    {
        $user = $request->user();
        $sender_id = $request->user_id;
        $id = $request->id;
        $auction = Auction::where('id', $id)->first();
        $auction->winner_id = $sender_id;
        $auction->save();
        $notification = new NotificationService($sender_id);
        $notification->send("
        پیشنهاد شما برای مزایده
        {$auction->title} 
        توسط 
        {$user->name}
        پذیرفته شد
        .");
        return true;
    }

    public function PayFee(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        
        $wallet = new WalletService($user);
        $balance = $wallet->getBalance();
        $id = $request->id;
        $auction = Auction::find($id);
        $options = Option::find(1);
        if ($options->site_share > $balance) {
            return json_encode(['msg' => "مبلغ کیف پول شما کمتر از هزینه مورد نیاز مناقصه است! لطفا کیف پول خود را شارژ نمایید."], JSON_UNESCAPED_UNICODE);
        }
        $wallet->withdraw($options->site_share);
        $percent = $options->deposit_percentage / 100;
        $wallet->withdraw($auction->price * $percent);
        return json_encode(['msg' => "هزینه با موفقیت از کیف پول شما پرداخت شد"], JSON_UNESCAPED_UNICODE);;
    }


    public function AuctionEnd(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        $wallet = new WalletService($user);
        $id = $request->id;
        $auction = Auction::find($id);
        $auction->is_active = 3;
        $auction->save();
        $options = Option::find(1);
        $percent = $options->deposit_percentage / 100;
        $wallet->deposit($auction->price * $percent);
        return json_encode(['msg' => "هزینه با موفقیت به کیف پول شما پرداخت شد"], JSON_UNESCAPED_UNICODE);;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        $options = Option::find(1);
        $wallet = new WalletService($user);
        $balance = $wallet->getBalance();
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => '',
            'user_id' => 'exists:users,id',
            'fields' => '',
            'category_id' => 'exists:categories,id',
            'address' => '',
            'pictures.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:8048',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);


        $validatedData['user_id'] = $user->id;
       
        if ($options->registration_fee > $balance) {
            return json_encode(['error' => "مبلغ کیف پول شما کمتر از هزینه ثبت مزایده است! لطفا کیف پول خود را شارژ نمایید."], JSON_UNESCAPED_UNICODE);
        }
        $wallet->withdraw($options->registration_fee);

        $auction = Auction::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'user_id' => $validatedData['user_id'],
            'fields' => $validatedData['fields'],
            'category_id' => $validatedData['category_id'],
            'address' => $validatedData['address'],
            'agent_id' => null,
            'picture' => "empty",
            'is_active' => false,
            'start' => $validatedData['start'],
            'end' => $validatedData['end'],
        ]);

        // Handle pictures
        if ($request->hasFile('pictures')) {
            $picturesPath = [];

            foreach ($request->file('pictures') as $index => $picture) {
                $path = $picture->store('auctions_pictures', 'public');
                $picturesPath[] = '/storage/' . $path;
            }

            // Save picture paths to the commodity
            $auction->update(['picture' => json_encode($picturesPath)]);
        }

        return response(['success' => "مناقصه شما ثبت شد و منتظر تایید برای انتشار است"], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auction = Auction::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser'])->findOrFail($id);
        return response(['auction' => $auction], Response::HTTP_OK);
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

    
    public function acceptAndPublish($id) {
        $auction = Auction::where('id', $id)->update(['is_active' => 1]);
        return $auction;
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
