<?php

namespace App\Services;

use App\Models\Treasury;
use App\Models\TreasuryTransaction;
use Illuminate\Support\Facades\DB;
use Exception;

class TreasuryService
{
    /**
     * معالجة الحركات المالية (إيداع / سحب) وتحديث الرصيد
     */
   // app/Services/TreasuryService.php

public function handleTransaction(array $data)
{
    return DB::transaction(function () use ($data) {
        $treasury = Treasury::lockForUpdate()->findOrFail($data['treasury_id']);
        $amount = $data['amount'];
        $type = $data['transaction_type'];

        // التحقق من الرصيد عند السحب
        if ($type === 'withdrawal' && $treasury->balance < $amount) {
            throw new \Exception("عذراً، الرصيد في خزينة ({$treasury->name}) غير كافٍ.");
        }

        // تحديث الرصيد
        $type === 'deposit'
            ? $treasury->increment('balance', $amount)
            : $treasury->decrement('balance', $amount);

        // توليد رقم حركة تلقائي إذا لم يرسل (18 رقم)
        // نستخدم الوقت الحالي + 4 أرقام عشوائية لضمان التفرد
        $generatedNo = $data['TransactionNo'] ?? (date('YmdHis') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT));

        return TreasuryTransaction::create([
            'treasury_id'      => $data['treasury_id'],
            'user_id'          => auth()->id(),
            'TransactionNo'    => $generatedNo, // DECIMAL(18, 0)
            'transaction_type' => $type,
            'amount'           => $amount,
            'financial_assistance_id' => $data['financial_assistance_id'] ?? null,
            'transaction_date' => $data['transaction_date'] ?? now(),
            'notes'            => $data['notes'] ?? null,
        ]);
    });
}
}
