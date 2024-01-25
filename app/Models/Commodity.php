<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commodity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'description', 'price', 'city_id', 'picture', 
        'decline', 'agent_id', 'expired_at', 'fields', 'published'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    
    public function agent()
    {
        return $this->morphOne(AgentDesk::class, 'agentable');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
