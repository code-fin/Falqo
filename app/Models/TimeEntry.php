<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TimeEntry extends Model { protected $guarded=[]; protected function casts(): array { return ['started_at'=>'datetime','ended_at'=>'datetime','billable'=>'boolean']; } public function project(): BelongsTo { return $this->belongsTo(Project::class); } public function task(): BelongsTo { return $this->belongsTo(Task::class); } public function getMinutesAttribute(): int { return (int) $this->started_at->diffInMinutes($this->ended_at ?? now()); } }
