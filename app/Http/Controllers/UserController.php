<?php

namespace App\Http\Controllers;

use App\Http\Services\NotificationService;
use App\Http\Services\WalletService;
use App\Models\AgentDesk;
use App\Models\AgentExpertise;
use App\Models\AgentInformation;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Commodity;
use App\Models\Tender;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereNot('role', 'agent')->with(['city', 'education'])->get();
        return $users;
    }

    public function agentList()
    {
        $users = User::where('role', 'agent')->with(['city', 'education', 'categoryExpertises.field', 'information'])->get();
        return $users;
    }

    /**
     * Display a listing of the resource.
     */
    public function agents()
    {
        $users = User::where('role', 'agent')->with(['city', 'education', 'information'])->get();
        return $users;
    }

    public function agentsByCategory($category_id)
    {
        $agents = User::where('role', 'agent')
            ->whereHas('categoryExpertises', function ($query) use ($category_id) {
                $query->where('field_id', $category_id);
            })
            ->with(['city', 'education', 'information', 'categoryExpertises'])
            ->get();

        return $agents;
    }

    /**
     * Display a listing of the resource.
     */
    public function agentInformationUpdate(Request $request, $agent_id)
    {
        $agentInformation = AgentInformation::where('agent_id', $agent_id)->update($request->all());

        return $agentInformation;
    }

    public function agentsIn(Request $request)
    {
        $user = $request->user();
        $user_id =  $user->id;

        $tenders = [];
        $auctions = [];
        $commodities = Commodity::with(['agent', 'user'])->where('agent_id', $user_id)->get();

        $response = [
            'tenders' => $tenders,
            'auctions' => $auctions,
            'commodities' => $commodities,
        ];

        return response($response, Response::HTTP_OK);
    }

    public function seenNotifications(Request $request)
    {
        $user = $request->user();
        $user_id =  $user->id;
        $notification = new NotificationService($user_id);
        $notification->seenAll();
    }

    public function agentsInCounter(Request $request)
    {
        $user = $request->user();
        $user_id =  $user->id;
        $notification = new NotificationService($user_id);
        $tendersCount = 0;
        $auctionsCount = 0;
        $commoditiesCount = Commodity::where('agent_id', $user_id)->whereNotIn('id', AgentDesk::pluck('agentable_id')->toArray())->count();

        $tendersCountDecline = 0;
        $auctionsCountDecline = 0;
        $commoditiesCountDecline = Commodity::where('user_id', $user_id)->whereNotNull('decline')->whereNotIn('id', AgentDesk::pluck('agentable_id')->toArray())->count();

        // Return the counts
        return [
            'tendersCount' => $tendersCount,
            'auctionsCount' => $auctionsCount,
            'commoditiesCount' => $commoditiesCount,
            'notificationsCount' => count($notification->list()),
            'tendersCountDecline' => $tendersCountDecline,
            'auctionsCountDecline' => $auctionsCountDecline,
            'commoditiesCountDecline' => $commoditiesCountDecline,
        ];
    }

    public function agentsInTitle(Request $request)
    {
        $user = $request->user();
        $user_id = $user->id;
        $notification = new NotificationService($user_id);
        $tenders = [];
        $auctions = [];
        $commodities = Commodity::where('agent_id', $user_id)->whereNotIn('id', AgentDesk::pluck('agentable_id')->toArray())->get(['id', 'title']);

        $tendersDecline = [];
        $auctionsDecline = [];
        $commoditiesDecline = Commodity::where('user_id', $user_id)->whereNotNull('decline')->whereNotIn('id', AgentDesk::pluck('agentable_id')->toArray())->get(['id', 'decline', 'title']);

        // Return the titles
        return [
            'tenders' => $tenders,
            'auctions' => $auctions,
            'commodities' => $commodities,
            'notifications' => $notification->list(),
            'tendersDecline' => $tendersDecline,
            'auctionsDecline' => $auctionsDecline,
            'commoditiesDecline' => $commoditiesDecline,
        ];
    }




    public function setRole(Request $request)
    {
        $user = $request->user();
        $categories = $request->categories;
        $departments = $request->departments;
        foreach ($categories as $key => $category_id) {
            $category = Category::find($category_id);
            $agentExpertise = new AgentExpertise();
            $agentExpertise->expertiese_id = $user->id;
            $agentExpertise->field()->associate($category);
            $agentExpertise->save();
        }

        $editedRole = User::where('id', $user->id)->update([
            'role' => $request->role,
            'state' => 'enabled',
        ]);
        if ($request->role === 'agent') {
            $agentInformation = new AgentInformation([
                "rate" => "0",
                "agent_id" => $user->id,
                'profile_photo_url' => '/profileplaceholder.png'

            ]);

            $agentInformation->save();
        }
        return $editedRole;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function AgentDesk(Request $request)
    {
        $agent = $request->user();
        $type = $request->type;
        $id = $request->id;
        if ($type === 'tender') {
            $tender = Tender::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id]);
            $tender->agent()->save($agentdesk);
        } else if ($type === 'auction') {
            $auction = Auction::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id]);
            $auction->agent()->save($agentdesk);
        } else {
            $commodity = Commodity::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id, 'fields' => $request->fields]);
            $commodity->agent()->save($agentdesk);
        }
        return true;
    }


    // agent decline
    public function AgentDecline(Request $request)
    {
        $agent = $request->user();
        $type = $request->type;
        $id = $request->id;
        if ($type === 'tender') {
            $tender = Tender::find($id);
            $tender->decline = $request->decline;
            $tender->agent_id = null;
            $tender->save();
        } else if ($type === 'auction') {
            $auction = Auction::find($id);
            $auction->decline = $request->decline;
            $auction->agent_id = null;
            $auction->save();
        } else {
            $commodity = Commodity::find($id);
            $commodity->decline = $request->decline;
            $commodity->agent_id = null;
            $commodity->save();
        }
        return true;
    }
    // agent set
    public function setagent(Request $request)
    {
        $user = $request->user();
        $type = $request->type;
        $id = $request->id;
        if ($type === 'tender') {
            $tender = Tender::find($id);
            $tender->agent_id = $request->agent_id;
            $tender->save();
        } else if ($type === 'auction') {
            $auction = Auction::find($id);
            $auction->agent_id = $request->agent_id;
            $auction->save();
        } else {
            $commodity = Commodity::find($id);
            $commodity->agent_id = $request->agent_id;
            $commodity->save();
        }
        return true;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::with(['city', 'education', 'information', 'employees', 'agentDesks.agentable'])->find($id);
        return $user;
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }


    public function checkProfile(Request $request)
    {
        $user = $request->user();
        if ($user->role == 'agent') {
            // Check if AgentInformation exists for the user
            $agentInformation = $user->information;

            if ($agentInformation && $agentInformation->profile_photo_url === '/profileplaceholder.png') {
                // Profile photo is set to the placeholder, return an error
                return response()->json(['retval' => false, 'error' => 'تصویر پروفایل خود را تنظیم کنید'], 400);
            }
            return response()->json(['retval' => true, 'error' => 'تصویر پروفایل خود را تنظیم کرده اید '], 200);
        }

        return response()->json(['retval' => true, 'error' => 'تصویر پروفایل خود را تنظیم کرده اید '], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $filteredData = $request->except(['education', 'city']);
        $updateUser = User::where('id', $id)->update($filteredData);
        if ($request->role === 'agent') {
            $agentExistsInformation = AgentInformation::where('agent_id', $id)->first();
            if (empty($agentExistsInformation)) {
                $agentInformation = new AgentInformation([
                    "rate" => "0",
                    "agent_id" => $id,
                    "is_active" => "deactive",
                    'profile_photo_url' => '/profileplaceholder.png'
                ]);

                $agentInformation->save();
            } else {
                $agentExistsInformation->is_active = "deactive";
                $agentExistsInformation->save();
            }
        }
        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function setPhotoAgent(Request $request)
    {
        // Retrieve the authenticated user using the token
        $user = $request->user();

        // Use $user->id to get the user_id

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        // Store the photo in the storage/app/public directory
        $picturePath = $request->file('photo')->store('profile_agent', 'public');
        // Save the photo path in the AgentInformation model
        $agentInformation = AgentInformation::where('agent_id', $user->id)->first();

        if (!$agentInformation) {
            $agentInformation = new AgentInformation(['agent_id' => $user->id]);
        }

        $agentInformation->profile_photo_url = '/storage/' . $picturePath;
        $agentInformation->is_active = 'deactive';
        $agentInformation->save();
        return response()->json(['message' => 'تصویر با موفقیت آپلود شد'], 200, [], JSON_UNESCAPED_UNICODE);
    }



    public function getCategoryExpertises(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
        $categoryExpertises = $user->categoryExpertises()->with('field')->get();

        return response()->json(['category_expertises' => $categoryExpertises]);
    }

    public function getCategoryExpertisesByid(Request $request, $agent_id)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $agent = User::where('id', $agent_id)->first();

        $categoryExpertises = $agent->categoryExpertises()->with('field')->get();

        return response()->json(['category_expertises' => $categoryExpertises]);
    }


    public function getDepartmentExpertises(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $departmentExpertises = $user->departmentExpertises()->with('field')->get();

        return response()->json(['department_expertises' => $departmentExpertises]);
    }

    public function handleSavePrice(Request $request, string $categoryId)
    {
        try {
            // Find the authenticated user
            $user = $request->user();

            // Find the category by ID
            $category = Category::findOrFail($categoryId);

            // Check if the user has the expertise associated with the category
            $agentExpertise = $user->agentExpertises()
                ->where('field_id', $category->id)
                ->first();

            if (!$agentExpertise) {
                return response(['error' => 'User does not have expertise for this category'], Response::HTTP_FORBIDDEN);
            }

            // Update the price in the agent expertise
            $agentExpertise->update(['price' => $request->input('price')]);

            return response(['message' => 'Category price updated successfully'], Response::HTTP_OK);
        } catch (\Exception $exception) {
            return response(['error' => $exception->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }




    // employee and education

    // Function to create a new education record for a user
    public function createEducation(Request $request, $userId)
    {
        // Validate the incoming request data
        $request->validate([
            'educational_level' => 'required|string',
            'field_of_study' => 'required|string',
            'educational_institution' => 'required|string',
            'from' => 'required|date',
            'to' => 'nullable|date',
            'currently_enrolled' => 'required|boolean',
        ]);

        // Find the user by ID
        $user = User::findOrFail($userId);

        // Create a new education record
        $education = $user->educations()->create($request->all());

        return response()->json($education, 201);
    }

    // Function to create a new employee record for a user
    public function createEmployee(Request $request, $userId)
    {
        // Validate the incoming request data
        $request->validate([
            'company_name' => 'required|string',
            'job_title' => 'required|string',
            'from' => 'required|date',
            'to' => 'nullable|date',
            'currently_enrolled' => 'required|boolean',
        ]);

        // Find the user by ID
        $user = User::findOrFail($userId);

        // Create a new employee record
        $employee = $user->employees()->create($request->all());

        return response()->json($employee, 201);
    }

    // Function to delete an education record for a user
    public function deleteEducation($userId, $educationId)
    {
        // Find the user by ID
        $user = User::findOrFail($userId);

        // Find the education record by ID for the given user
        $education = $user->educations()->findOrFail($educationId);

        // Delete the education record
        $education->delete();

        return response()->json(['message' => 'Education record deleted successfully'], 200);
    }

    // Function to delete an employee record for a user
    public function deleteEmployee($userId, $employeeId)
    {
        // Find the user by ID
        $user = User::findOrFail($userId);

        // Find the employee record by ID for the given user
        $employee = $user->employees()->findOrFail($employeeId);

        // Delete the employee record
        $employee->delete();

        return response()->json(['message' => 'Employee record deleted successfully'], 200);
    }


    public function WalletBalance(Request $request)
    {
        $user = $request->user();
        if (!$user) throw new Exception("User Not found!", 1);

        $wallet = new WalletService($user);
        return $wallet->getBalance();
    }
}
