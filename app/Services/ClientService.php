<?php

namespace App\Services;

use App\Models\Client;
use Illuminate\Support\Facades\Storage;

class ClientService
{
    /**
     * تخزين بيانات عميل جديد مع معالجة الصورة.
     */
    public function createClient(array $data): Client
    {
        if (isset($data['image'])) {
            $data['image'] = $this->uploadImage($data['image']);
        }

        return Client::create($data);
    }

    /**
     * تحديث بيانات عميل حالي مع معالجة استبدال الصورة.
     */
    public function updateClient(Client $client, array $data): Client
    {
        if (isset($data['image'])) {
            // حذف الصورة القديمة إذا وجدت لتوفير المساحة
            if ($client->image) {
                Storage::disk('public')->delete($client->image);
            }
            $data['image'] = $this->uploadImage($data['image']);
        }

        $client->update($data);

        return $client;
    }

    /**
     * حذف العميل (Soft Delete) وتنظيف الصورة إن لزم الأمر مستقبلاً (حالياً نتركها في الـ SoftDelete).
     */
    public function deleteClient(Client $client): void
    {
        $client->delete();
    }

    /**
     * دالة مساعدة لرفع الصور.
     */
    private function uploadImage($image): string
    {
        // تخزين الصورة في مجلد clients داخل الـ public disk
        return $image->store('clients', 'public');
    }
}
