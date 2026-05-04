<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryDraw;
use App\Http\Resources\Api\LotteryDrawResource;
use App\Services\LotteryService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Exception;

class LotteryController extends Controller
{
    private LotteryService $lotteryService;

    public function __construct(LotteryService $lotteryService)
    {
        $this->lotteryService = $lotteryService;
    }

    /**
     * عرض سجل السحوبات السابقة.
     */
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', LotteryDraw::class);

        // جلب السحوبات مع بيانات العملاء الفائزين مرتبة من الأحدث
        $draws = LotteryDraw::with('client')->latest()->paginate(15);

        return LotteryDrawResource::collection($draws);
    }

    /**
     * إجراء عملية السحب الأوتوماتيكية.
     */
    public function store()
    {
        Gate::authorize('create', LotteryDraw::class);

        try {
            $draw = $this->lotteryService->conductDraw();

            return response()->json([
                'message' => 'تم إجراء السحب بنجاح.',
                'data' => new LotteryDrawResource($draw)
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'message' => 'حدث خطأ أثناء إجراء السحب.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * إلغاء/حذف سحب معين.
     */
    public function destroy(LotteryDraw $lottery_draw) // استخدمنا snake_case لتطابق مع الـ Route Parameter
    {
        Gate::authorize('delete', $lottery_draw);

        $this->lotteryService->cancelDraw($lottery_draw);

        return response()->json(['message' => 'تم إلغاء السحب بنجاح.']);
    }
}
