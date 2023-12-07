<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'role',
        'category_id',
        'phonenumber',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function education()
    {
        return $this->hasMany(Education::class);
    }

    public function agentExpertises()
    {
        return $this->hasMany(AgentExpertise::class, 'expertiese_id');
    }

    public function categoryExpertises()
    {
        return $this->agentExpertises()->where(function ($query) {
            $query->where('field_type', '=', "App\\Models\\Category");
        });
    }

    public function departmentExpertises()
    {
        return $this->agentExpertises()->where(function ($query) {
            $query->where('field_type', '=', "App\\Models\\Department");
        });
    }

    public function information()
    {
        return $this->hasOne(AgentInformation::class, 'agent_id');
    }


    public function educations()
    {
        return $this->hasMany(Education::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
