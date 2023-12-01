<?php

namespace App\Http\Controllers;

use App\Models\AgentDesk;
use App\Models\AgentExpertise;
use App\Models\Auction;
use App\Models\Category;
use App\Models\Commodity;
use App\Models\Department;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with(['city', 'education'])->get();
        return $users;
    }

    public function agentList(){
        $users = User::where('role', 'agent')->with(['city', 'education', 'departmentExpertises.field', 'categoryExpertises.field'])->get();
        return $users;
    }

        /**
     * Display a listing of the resource.
     */
    public function agents()
    {
        $users = User::where('role', 'agent')->with(['city', 'education'])->get();
        return $users;
    }

    public function agentsIn(Request $request)
    {
        $user = $request->user();
        $user_id =  $user->id;

        $tenders = Tender::with('agent')->where('agent_id', $user_id)->get();
        $auctions = Auction::with('agent')->where('agent_id', $user_id)->get();
        $commodities = Commodity::with('agent')->where('agent_id', $user_id)->get();

        $response = [
            'tenders' => $tenders,
            'auctions' => $auctions,
            'commodities' => $commodities,
        ];

        return response($response, Response::HTTP_OK);
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

        foreach ($departments as $key => $department_id) {
            $department = Department::find($department_id);
            $agentExpertise = new AgentExpertise();
            $agentExpertise->expertiese_id = $user->id;
            $agentExpertise->field()->associate($department);
            $agentExpertise->save();
        }

        $editedRole = User::where('id', $user->id)->update([
            'role' => $request->role,
            'state' => 'enabled',
        ]);
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
        if($type === 'tender'){
            $tender = Tender::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id]);
            $tender->agent()->save($agentdesk);
        }
        else if($type === 'auction'){
            $auction = Auction::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id]);
            $auction->agent()->save($agentdesk);
        }
        else {
            $commodity = Commodity::find($id);
            $agentdesk = new AgentDesk(['description' => $request->description, 'agent_id' => $agent->id]);
            $commodity->agent()->save($agentdesk);
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
        $user = User::with(['city', 'education'])->find($id);
        return $user;
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
        $updateUser = User::where('id', $id)->update($request->all());
        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
