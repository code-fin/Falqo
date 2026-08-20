<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Customer extends Model { protected $guarded=[]; public function projects(): HasMany { return $this->hasMany(Project::class); } public function tickets(): HasMany { return $this->hasMany(Ticket::class); } }
