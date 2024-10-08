<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    protected $table = 'study_programs';
    protected $primaryKey = 'id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;
    protected $fillable = [
        'name',
    ];
    // public function users(): HasMany
    // {
    //     return $this->hasMany(User::class, 'study_program_id', 'id');
    // }
    // public function study_field(): BelongsTo
    // {
    //     return $this->belongsTo(StudyField::class, 'study_field_id', 'id');
    // }
}
