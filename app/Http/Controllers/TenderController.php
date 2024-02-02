<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\WalletService;
use App\Models\Option;
use App\Models\Purpose;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Tender;
use App\Models\User;
use Exception;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tenders = Tender::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser'])->where('is_active', 1)->orderBy('id', 'DESC')->get();
        return response(['tenders' => $tenders], Response::HTTP_OK);
    }
    /**
     * Display a listing of the resource.
     */
    public function indexUnPublished()
    {
        $tenders = Tender::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser', 'winner'])->orderBy('is_active', 'ASC')->get();
        return response(['tenders' => $tenders], Response::HTTP_OK);
    }
    /**
     * Display a listing of the resource.
     */
    public function indexByUser(Request $request)
    {
        $user = $request->user();
        $tenders = Tender::where('user_id', $user->id)->with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser', 'winner'])->orderBy('id', 'DESC')->get();
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
        $purpose = new Purpose(['description' => $request->description, 'user_id' => $user->id, 'price' => $request->price]);
        $notification = new NotificationService($tender->user_id);
        $notification->send("پیشنهاد جدید به مناقصه {$tender->title} توسط {$user->name} افزوده شد.");
        $tender->purpose()->save($purpose);
        return true;
    }

    public function PurposeAccept(Request $request)
    {
        $user = $request->user();
        $sender_id = $request->user_id;
        $id = $request->id;
        $tender = Tender::where('id', $id)->first();
        $tender->winner_id = $sender_id;
        $tender->save();
        $notification = new NotificationService($sender_id);
        $notification->send("
        پیشنهاد شما برای مناقصه
        {$tender->title} 
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
        $tender = Tender::find($id);
        $options = Option::find(1);
        if ($options->site_share > $balance) {
            return json_encode(['msg' => "مبلغ کیف پول شما کمتر از هزینه مورد نیاز مناقصه است! لطفا کیف پول خود را شارژ نمایید."], JSON_UNESCAPED_UNICODE);
        }
        $wallet->withdraw($options->site_share);
        $percent = $options->deposit_percentage / 100;
        $wallet->withdraw($tender->price * $percent);
        return json_encode(['msg' => "هزینه با موفقیت از کیف پول شما پرداخت شد"], JSON_UNESCAPED_UNICODE);;
    }


    public function TenderEnd(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        $id = $request->id;
        $tender = Tender::find($id);
        $tender->is_active = 5;
        $tender->save();
      
        return json_encode(['msg' => "درخواست پایان مناقصه ثبت شد"], JSON_UNESCAPED_UNICODE);;
    }

    public function TenderEndCanceling(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        $id = $request->id;
        $tender = Tender::find($id);
        $tender->is_active = 2;
        Purpose::where(['user_id' => $tender->winner_id, 'purposeable_id' => $id])->delete();
        $tender->winner_id = null;
        $tender->save();
      
        return json_encode(['msg' => "درخواست رد برنده مناقصه  ثبت شد"], JSON_UNESCAPED_UNICODE);;
    }

    public function TenderEndAdmin(Request $request)
    {
        $user = User::find($request->user_id);
        if(!$user) throw new Exception("Error Processing Request", 1);
        $wallet = new WalletService($user);
        $id = $request->id;
        $tender = Tender::find($id);
        $options = Option::find(1);
        $percent = $options->deposit_percentage / 100;
        $purposes = Purpose::where(['purposeable_id' => $id, 'purposeable_type' => 'App\Models\Tender'])->get();
        foreach ($purposes as $key => $purpose) {
            if($tender->winner_id !== $purpose->user_id)
            {
                $user = User::find($purpose->user_id);
                $user_wallet = new WalletService($user);
                $user_wallet->deposit($tender->price * $percent);
            }
        }
        $tender->is_active = 3;
        $tender->save();
        $price = $tender->price * $percent;
        return $price;
        $wallet->deposit($price);
        return json_encode(['msg' => "هزینه با موفقیت به کیف پول ها پرداخت شد"], JSON_UNESCAPED_UNICODE);;
    }

  



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
            return json_encode(['error' => "مبلغ کیف پول شما کمتر از هزینه ثبت مناقصه است! لطفا کیف پول خود را شارژ نمایید."], JSON_UNESCAPED_UNICODE);
        }
        $wallet->withdraw($options->registration_fee);

        $tender = Tender::create([
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
                $path = $picture->store('tenders_pictures', 'public');
                $picturesPath[] = '/storage/' . $path;
            }

            // Save picture paths to the commodity
            $tender->update(['picture' => json_encode($picturesPath)]);
        }

        return response(['success' => "مناقصه شما ثبت شد و منتظر تایید برای انتشار است"], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tender = Tender::with(['agent.agent', 'user', 'category', 'purpose.user', 'agentUser'])->findOrFail($id);
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
        if($request->agent_id)
        $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:categorys,id',
            'agent_id' => 'required|exists:users,id',
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]); else $validatedData = $request->validate([
            'user_id' => 'sometimes|exists:users,id',
            'category_id' => 'sometimes|exists:categorys,id',
          
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $tender = Tender::findOrFail($id);
        $tender->update($validatedData);

        return response(['tender' => $tender], Response::HTTP_OK);
    }

    public function acceptAndPublish($id) {
        $tender = Tender::where('id', $id)->update(['is_active' => 1]);
        return $tender;
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
