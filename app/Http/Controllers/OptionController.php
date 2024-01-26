<?php

namespace App\Http\Controllers;

use App\Http\Services\OptionService;
use Illuminate\Http\Request;

class OptionController extends Controller
{
    private $optionService;

    public function __construct(OptionService $optionService)
    {
        $this->optionService = $optionService;
    }

    public function index()
    {
        $options = $this->optionService->getAllOptions();

        return response()->json(['data' => $options], 200);
    }

    public function show($id)
    {
        $option = $this->optionService->getOptionById($id);

        return response()->json(['data' => $option], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_share' => 'required|numeric',
            'registration_fee' => 'required|numeric',
            'deposit_percentage' => 'required|numeric',
        ]);

        $option = $this->optionService->createOption($data);

        return response()->json(['message' => 'Option created successfully', 'data' => $option], 201);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'site_share' => 'numeric',
            'registration_fee' => 'numeric',
            'deposit_percentage' => 'numeric',
        ]);

        $option = $this->optionService->updateOption($id, $data);

        return response()->json(['message' => 'Option updated successfully', 'data' => $option], 200);
    }

    public function destroy($id)
    {
        $option = $this->optionService->deleteOption($id);

        return response()->json(['message' => 'Option deleted successfully', 'data' => $option], 200);
    }
}