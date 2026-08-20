<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Task extends Model { protected $guarded=[]; protected function casts(): array { return ['due_date'=>'date']; } public function project(): BelongsTo { return $this->belongsTo(Project::class); } public function ticket(): BelongsTo { return $this->belongsTo(Ticket::class); } }
