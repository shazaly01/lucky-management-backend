<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LotteryDraw;
use App\Http\Resources\Api\LotteryDrawResource;
use App\Services\LotteryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Exception;

class LotteryController extends Controller
{
    private LotteryService $lotteryService;

    /**
     * حقن الخدمة داخل المتحكم.
     */
    public function __construct(LotteryService $lotteryService)
    {
        $this->lotteryService = $lotteryService;
    }

    /**
     * عرض قائمة الفائزين مع دعم الفلترة بالتاريخ والصفحات.
     * المسار: GET /api/lottery-draws?date=2026-05-04
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', LotteryDraw::class);

        // جمع الفلاتر من الطلب
        $filters = $request->only(['date', 'per_page']);

        // جلب البيانات عبر الخدمة
        $draws = $this->lotteryService->getPaginatedDraws($filters);

        return LotteryDrawResource::collection($draws);
    }

    /**
     * إجراء عملية سحب جديدة.
     * المسار: POST /api/lottery-draws
     */
    public function store(): JsonResponse
    {
        Gate::authorize('create', LotteryDraw::class);

        try {
            $draw = $this->lotteryService->conductDraw();

            return response()->json([
                'status' => true,
                'message' => 'تم إجراء السحب بنجاح واختيار الفائز.',
                'data' => new LotteryDrawResource($draw)
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'تعذر إجراء السحب.',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * إلغاء سحب معين (سيؤدي لعودة العميل لقائمة المؤهلين للسحب).
     * المسار: DELETE /api/lottery-draws/{lottery_draw}
     */
    public function destroy(LotteryDraw $lottery_draw): JsonResponse
    {
        Gate::authorize('delete', $lottery_draw);

        $this->lotteryService->cancelDraw($lottery_draw);

        return response()->json([
            'status' => true,
            'message' => 'تم إلغاء السحب بنجاح، العميل متاح الآن في القرعة القادمة.'
        ]);
    }
}
