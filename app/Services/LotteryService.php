<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LotteryDraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;

class LotteryService
{
    /**
     * جلب قائمة السحوبات مع إمكانية الفلترة بالتاريخ.
     *
     * @param array $filters
     * @return LengthAwarePaginator
     */
    public function getPaginatedDraws(array $filters = []): LengthAwarePaginator
    {
        $query = LotteryDraw::with('client')->latest();

        // فلترة النتائج بناءً على التاريخ إذا تم تمريره
        if (!empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    /**
     * إجراء عملية السحب العشوائي بآلية آمنة ضد التزامن.
     *
     * @return LotteryDraw
     * @throws Exception
     */
    public function conductDraw(): LotteryDraw
    {
        $lock = Cache::lock('lottery_draw_process', 5);

        if ($lock->get()) {
            try {
                return DB::transaction(function () {
                    // استبعاد العملاء الذين لديهم سجل فوز نشط (غير محذوف SoftDeleted)
                    $winner = Client::doesntHave('lotteryDraw')
                        ->inRandomOrder()
                        ->first();

                    if (!$winner) {
                        throw new Exception('لا يوجد عملاء متاحين للسحب حالياً.');
                    }

                    return LotteryDraw::create([
                        'client_id' => $winner->id,
                    ])->load('client');
                });
            } finally {
                $lock->release();
            }
        }

        throw new Exception('هناك عملية سحب تجري حالياً، الرجاء المحاولة مرة أخرى.');
    }

    /**
     * حذف أو إلغاء سحب معين.
     *
     * @param LotteryDraw $lotteryDraw
     * @return void
     */
    public function cancelDraw(LotteryDraw $lotteryDraw): void
    {
        $lotteryDraw->delete();
    }
}
