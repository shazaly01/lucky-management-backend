<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * الحقول القابلة للتعبئة.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'image',
    ];

    /**
     * علاقة العميل بجدول سحوبات القرعة.
     * العميل يمكن أن يكون له فوز واحد في السحب (بناءً على قاعدة استبعاد الفائزين).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function lotteryDraw(): HasOne
    {
        return $this->hasOne(LotteryDraw::class, 'client_id');
    }
}
