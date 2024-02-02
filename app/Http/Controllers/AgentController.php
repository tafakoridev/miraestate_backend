<?php

namespace App\Http\Controllers;

use App\Models\AgentDesk;
use App\Models\AgentExpertise;
use App\Models\AgentInformation;
use App\Models\Commodity;
use App\Models\Education;
use App\Models\User;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public const UNDER_DIPLOMA = 0;
    public const DIPLOMA = 1;
    public const OVER_DIPLOMA = 2;
    public const BRANCH = 3;
    public const MASTER = 4;
    public const PHD = 5;

    static function getEducationalLevelValue($string)
    {
        switch ($string) {
            case 'under_diploma':
                return AgentController::UNDER_DIPLOMA;
            case 'diploma':
                return AgentController::DIPLOMA;
            case 'over_diploma':
                return AgentController::OVER_DIPLOMA;
            case 'branch':
                return AgentController::BRANCH;
            case 'master':
                return AgentController::MASTER;
            case 'phd':
                return AgentController::PHD;
            default:
                // Handle the case when the string is not recognized
                return null;
        }
    }

    function clientRateAgent(Request $request)
    {
        $user = $request->user();
        $agentDesk = AgentDesk::where(['agentable_id' => $request->id])->first();
        $commodity = Commodity::where('user_id', $user->id)->where('id', $request->id)->count();
        if ($commodity) {
            $agentDesk->comment = $request->comment;
            $agentDesk->rate = $request->rate;
            $agentDesk->save();
            return response()->json(['result' => "updated", "retval" => true], 200);
        }
        return response()->json(['result' => "Not user agent", "retval" => false], 401);
    }

    function getAllRowsWithCommentAndRate()
    {
        $comments = AgentDesk::whereNotNull('comment')
            ->whereNotNull('rate')
            ->with('agent')
            ->get();
        return $comments;
    }

    static function rating()
    {
        $agents = User::where('role', 'agent')->get();
        foreach ($agents as $key => $agent) {
            $values = [];
            $educations = Education::where('user_id', $agent->id)
                ->orderByRaw("FIELD(educational_level, 'under_diploma', 'diploma', 'over_diploma', 'branch', 'master', 'phd')")
                ->get();
            if (count($educations)) {
                $level = $educations[count($educations) - 1]->educational_level;
                array_push($values, AgentController::getEducationalLevelValue($level));
            }
            $agentDesks = AgentDesk::where('agent_id', $agent->id)->count();
            array_push($values, $agentDesks);
            $values = array_sum($values);
            AgentInformation::where('agent_id', $agent->id)->update(['rate' => $values]);
        }
    }

    static function agentRate($agentId)
    {
        $agent = AgentInformation::where('agent_id', $agentId)->first();
        if ($agent)
            return $agent->rate;
        else return false;
    }

    static function bestAgent($categoryId)
    {
        $agentDesksIds = AgentDesk::get()->pluck('agentable_id');
        $commoditiesIds = Commodity::whereNotIn('id', $agentDesksIds)->get();
        $asirAgents = array_values(array_unique($commoditiesIds->pluck('agent_id')->all()));
        $numericAsir = [];
        foreach ($asirAgents as $agentasir) {
            if (is_numeric($agentasir))
                array_push($numericAsir, $agentasir);
        }

        $relatedAgents = AgentExpertise::where(['field_type' => 'App\Models\Category', 'field_id' => $categoryId])->whereNotIn('expertiese_id', $numericAsir)->get()->pluck('expertiese_id');
        $relatedAgents = array_values(array_unique($relatedAgents->all()));
        $rates = [];

        foreach ($relatedAgents as $agentId) {
            $rate = AgentController::agentRate($agentId);
            $rates[] = ["id" => $agentId, "rate" => (int) $rate];
        }

        $compareByRate = function ($a, $b) {
            return $b['rate'] <=> $a['rate'];
        };
        $selected = null;
        // Use usort without returning its result
        usort($rates, $compareByRate);
        if (count($rates))
            $selected = $rates[0];
        if ($selected) {
            $selectedAgent = User::find($selected['id']);

            $user = $selectedAgent;
            $agentInformation = $user->information;
            if ($agentInformation && $agentInformation->profile_photo_url === '/profileplaceholder.png') {
                // Profile photo is set to the placeholder, return an error
                if($rates[1]) $selectedAgent = User::find($rates[1]['id']);
            }
            return $selectedAgent;
        }
        $selectedAgent = null;
        $relatedAgents = AgentExpertise::where(['field_type' => 'App\Models\Category', 'field_id' => $categoryId])->get()->pluck('expertiese_id');
        if (!empty($relatedAgents[0]))
            $selectedAgent = User::find($relatedAgents[0]);
            $user = $selectedAgent;
            $agentInformation = $user->information;
            if ($agentInformation && $agentInformation->profile_photo_url === '/profileplaceholder.png') {
                // Profile photo is set to the placeholder, return an error
                if($relatedAgents[1]) $selectedAgent = User::find($relatedAgents[1]['id']);
                else  $selectedAgent = null;
            }
      
        // Check if AgentInformation exists for the user




        return $selectedAgent;
    }
}
