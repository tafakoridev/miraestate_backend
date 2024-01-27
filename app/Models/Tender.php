<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tender extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'user_id',
        'category_id',
        'agent_id',
        'title',
        'description',
        'decline',
        'address',
        'price',
        'fields',
        'picture',
        'start',
        'end',
        'is_active',
    ];

    public function agentUser()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function agent()
    {
        return $this->morphOne(AgentDesk::class, 'agentable');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function purpose()
    {
        return $this->morphMany(Purpose::class, 'purposeable')->orderBy('price', 'desc'); // or 'desc' for descending order
    }
    

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
