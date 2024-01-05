<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
        ,'parent_id'
        ,'price'
    ];

    public function agentExpertises()
    {
        return $this->morphMany(AgentExpertise::class, 'field');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function recursiveChildren()
    {
        return $this->children()->with('recursiveChildren');
    }
}
