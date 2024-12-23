<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Loan extends Model
{
    protected $table = 'loans';
    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = true;

    protected $fillable = [
        'member_id',
        'librarian_id',
        'loan_date',
        'return_date',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id', 'id');
    }

    public function librarian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'librarian_id', 'id');
    }
}
