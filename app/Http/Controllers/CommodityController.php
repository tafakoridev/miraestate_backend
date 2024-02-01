<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\WalletService;
use App\Models\AgentDesk;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Commodity;
use App\Models\Tender;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;


class CommodityController extends Controller
{
    const Commodity = "commodity";
    const Tender = "tender";
    const Auction = "auction";

    public function index()
    {
        $commodities = Commodity::with(['city', 'category', 'agent.agent', 'user'])
            // ->where('expired_at', '>', now()) // Retrieve records where expired_at is in the future
           
            ->where('published', '2') // Retrieve records where expired_at is in the future
            ->orderBy("id", 'DESC')
            ->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function adminChangePublish(Request $request, $id)
    {
        Commodity::where('id', $id)->update(['published' => 2, 'price'=> $request->price]);
        return response(['retval' => true], Response::HTTP_OK);
    }

    public function clientChangePublish(Request $request, $id)
    {
        $user = $request->user();
        $result = Commodity::where(['id' => $id, 'user_id' => $user->id])->update(['published' => 1]);
        return response(['retval' => $result], Response::HTTP_OK);
    }

    public function indexClientCartable(Request $request)
    {
        $user = $request->user();
        $commodities = Commodity::with(['city', 'category', 'agent.agent', 'user'])
            ->where('user_id', $user->id) // Retrieve records where expired_at is in the future
            ->where('published', '0') // Retrieve records where expired_at is in the future
            ->orderBy("id", 'DESC')
            ->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function indexAdminCartable()
    {
        $commodities = Commodity::with(['city', 'category', 'agent.agent', 'user'])
            ->whereNotNull('agent_id')
            ->orderBy("id", 'DESC')
            ->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function AcceptByAdmin(Request $request)
    {
        $commodities = AgentDesk::where(['agentable_id' => $request->id])->update(['accepted' => 1]);
        return response(['retval' => true], Response::HTTP_OK);
    }

    public function indexByCity($city_id)
    {
        $commodities = Commodity::where('city_id', $city_id)->with(['city', 'category', 'agent.agent',])->where('expired_at', '>', now())->get();
        return response(['commodities' => $commodities], Response::HTTP_OK);
    }

    public function indexByCityAndType($city_id, $type)
    {
        if ($type === CommodityController::Auction)
            $items = Auction::with(['category', 'agent.agent',])->where('is_active', 1)->get();
        if ($type === CommodityController::Tender)
            $items = Tender::with(['category', 'agent.agent',])->where('is_active', 1)->get();
        else if ($type === CommodityController::Commodity)
            $items = Commodity::where('city_id', $city_id)->with(['city', 'category', 'agent.agent',])->where('expired_at', '>', now())->get();
        return response(['commodities' => $items], Response::HTTP_OK);
    }

    function extractIds($item, &$idArray)
    {
        $idArray[] = $item['id'];

        if (!empty($item['recursive_children'])) {
            foreach ($item['recursive_children'] as $child) {
                $this->extractIds($child, $idArray);
            }
        }
    }

    public function indexByCityAndTypeAndCategory($city_id, $type, $category_id)
    {
        $category = Category::with("recursiveChildren")->where('id', $category_id)->first();
        $data = json_decode(json_encode($category), true, 512, JSON_UNESCAPED_UNICODE);
        // Initialize the array to store IDs
        $idArray = [];
        $items = Commodity::where('city_id', $city_id)->with(['city', 'category', 'agent.agent',])->whereIn("category_id", $idArray)->where('expired_at', '>', now())->get();
        // Call the recursive function for the main item
        $this->extractIds($data, $idArray);
        if ($type === CommodityController::Auction)
            $items = Auction::with(['category', 'agent.agent',])->whereIn("category_id", $idArray)->where('is_active', 1)->get();
        if ($type === CommodityController::Tender)
            $items = Tender::with(['category', 'agent.agent',])->whereIn("category_id", $idArray)->where('is_active', 1)->get();
        else if ($type === CommodityController::Commodity)
            $items = Commodity::where('city_id', $city_id)->with(['city', 'category', 'agent.agent',])->whereIn("category_id", $idArray)->where('expired_at', '>', now())->get();
        return response(['commodities' => $items], Response::HTTP_OK);
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
        if ($request->agent_id)
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
        if ($request->has('picture'))
            $picturePath = $request->file('picture')->store('commodity_pictures', 'public');

        $commodity = Commodity::create([
            'user_id' => $validatedData['user_id'],
            'category_id' => $validatedData['category_id'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'city_id' => $validatedData['city_id'],
            'agent_id' => $validatedData['agent_id'] ?? null,
            'picture' => $request->has('picture') ? '/storage/' . $picturePath : "",
            'expired_at' => Carbon::now()->addDays(30)
        ]);

        return response(['commodity' => $commodity], Response::HTTP_CREATED);
    }

    public function AgentReviewed(Request $request) {
        $user = $request->user();
        $commodity = Commodity::where(['id' => $request->id, 'user_id' => $user->id])->first();
        $commodity->local = 3;
        $commodity->save();
        $notification = new NotificationService($commodity->agent_id);
        $notification->send("
        بازدید در محل  کارشناسی 
        {$commodity->title} 
        توسط
         {$user->name} تایید شد");
        return response(['retval' => true], Response::HTTP_CREATED);
    }


    public function storeEx(Request $request)
    {
        $user = $request->user();
        if(!$user) throw new Exception("Error Processing Request", 1);
        
        $wallet = new WalletService($user);
        $balance = $wallet->getBalance();
        $validatedData = $request->validate([
            'title' => 'string|max:255',
            'description' => 'string',
            'price' => '',
            'local' => '',
            'fields' => '',
            'category_id' => 'exists:categories,id',
            'city_id' => 'exists:cities,id',
            'pictures.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:8048',
        ]);


        $validatedData['user_id'] = $user->id;
        $agent = AgentController::bestAgent($validatedData['category_id']);

        if (!$agent) {
            return json_encode(['error' => "کارشناس مرتبط برای  شما پیدا نشد، با پشتیبانی تماس بگیرید"], JSON_UNESCAPED_UNICODE);
        }

        $category = Category::find($validatedData['category_id']);
       
        if ($category->price > $balance) {
            return json_encode(['error' => "مبلغ کیف پول شما کمتر از هزینه کارشناسی است! لطفا کیف پول خود را شارژ نمایید."], JSON_UNESCAPED_UNICODE);
        }
        $wallet->withdraw($category->price);
        $agent_id = $agent->id;

        $commodity = Commodity::create([
            'user_id' => $validatedData['user_id'],
            'category_id' => $validatedData['category_id'],
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'price' => $validatedData['price'],
            'city_id' => $validatedData['city_id'],
            'agent_id' => $agent_id,
            'local' => $validatedData['local'],
            'picture' => "empty",
            'fields' => $validatedData['fields'],
            'expired_at' => Carbon::now()->addDays(30)
        ]);

        // Handle pictures
        if ($request->hasFile('pictures')) {
            $picturesPath = [];

            foreach ($request->file('pictures') as $index => $picture) {
                $path = $picture->store('commodity_pictures', 'public');
                $picturesPath[] = '/storage/' . $path;
            }

            // Save picture paths to the commodity
            $commodity->update(['picture' => json_encode($picturesPath)]);
        }

        return response(['success' => "کارشناسی شما به $agent->name سپرده شد."], Response::HTTP_CREATED);
    }
}
