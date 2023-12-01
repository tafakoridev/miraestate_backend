<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    public function agentExpertises()
    {
        return $this->morphMany(AgentExpertise::class, 'field');
    }
}
