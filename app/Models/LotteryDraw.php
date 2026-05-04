<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LotteryDraw extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * الحقول القابلة للتعبئة.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'client_id',
    ];

    /**
     * علاقة السحب بالعميل الفائز.
     * كل سجل سحب يعود لعميل واحد.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
