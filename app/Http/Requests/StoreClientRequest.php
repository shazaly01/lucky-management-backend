<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:clients,phone',
            'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'اسم العميل',
            'phone' => 'رقم الهاتف',
            'image' => 'الصورة الشخصية',
        ];
    }

    /**
     * تخصيص رسائل الخطأ باللغة العربية
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.unique' => 'رقم الهاتف مسجل لدينا مسبقاً، الرجاء إدخال رقم آخر.',
            // يمكنك إضافة المزيد من الرسائل المخصصة هنا إذا أردت مستقبلاً مثل:
            // 'name.required' => 'حقل الاسم مطلوب ولا يمكن تركه فارغاً.',
        ];
    }
}
