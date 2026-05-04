<?php

namespace App\Services;

use App\Models\Client;
use App\Models\LotteryDraw;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Exception;

class LotteryService
{
    /**
     * إجراء عملية السحب العشوائي بآلية آمنة ضد التزامن.
     *
     * @return LotteryDraw
     * @throws Exception
     */
    public function conductDraw(): LotteryDraw
    {
        // استخدام قفل (Lock) لمنع أي موظف آخر من إجراء سحب في نفس اللحظة (لمدة 5 ثوانٍ)
        $lock = Cache::lock('lottery_draw_process', 5);

        if ($lock->get()) {
            try {
                // تغليف العملية داخل Database Transaction لضمان سلامة البيانات
                return DB::transaction(function () {
                    // استبعاد العملاء الذين فازوا مسبقاً
                    // دالة doesntHave تبحث عن العملاء الذين ليس لديهم سجل مرتبط في علاقة lotteryDraw
                    // هذه الدالة تتعامل مع الـ SoftDeletes بذكاء (العميل الذي تم حذف سحبه سيعود مؤهلاً)
                    $winner = Client::doesntHave('lotteryDraw')
                        ->inRandomOrder()
                        ->first();

                    if (!$winner) {
                        throw new Exception('لا يوجد عملاء متاحين للسحب. إما أن الجميع فازوا مسبقاً أو لا يوجد عملاء مسجلين.');
                    }

                    // إنشاء سجل الفوز
                    $draw = LotteryDraw::create([
                        'client_id' => $winner->id,
                    ]);

                    // تحميل بيانات العميل لارجاعها مباشرة في الـ API
                    return $draw->load('client');
                });
            } finally {
                // تحرير القفل فور الانتهاء سواء نجحت العملية أو فشلت
                $lock->release();
            }
        }

        throw new Exception('هناك عملية سحب تجري حالياً بالفعل، الرجاء الانتظار للحظات.');
    }

    /**
     * حذف أو إلغاء سحب معين (Soft Delete).
     *
     * @param LotteryDraw $lotteryDraw
     * @return void
     */
    public function cancelDraw(LotteryDraw $lotteryDraw): void
    {
        $lotteryDraw->delete();
    }
}
