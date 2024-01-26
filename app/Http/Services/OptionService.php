<?php
namespace App\Http\Services;

use App\Models\Option;

class OptionService
{
    public function createOption(array $data)
    {
        return Option::create($data);
    }

    public function getAllOptions()
    {
        return Option::all();
    }

    public function getOptionById($id)
    {
        return Option::findOrFail($id);
    }

    public function updateOption($id, array $data)
    {
        $option = Option::findOrFail($id);
        $option->update($data);

        return $option;
    }

    public function deleteOption($id)
    {
        $option = Option::findOrFail($id);
        $option->delete();

        return $option;
    }
}