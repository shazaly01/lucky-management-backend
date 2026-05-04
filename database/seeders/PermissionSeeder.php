<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. إعادة تعيين ذاكرة الصلاحيات المؤقتة لضمان تطبيق التغييرات فوراً
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'api';

        // 2. قائمة شاملة بكافة الصلاحيات المطلوبة لنظام القرعة
        $permissions = [
            'dashboard.view',

            // إدارة المستخدمين (لمديري النظام)
            'user.view', 'user.create', 'user.update', 'user.delete',
            'role.view', 'role.create', 'role.update', 'role.delete',

            // إدارة العملاء (النزلاء)
            'client.view', 'client.create', 'client.update', 'client.delete',

            // إدارة سحوبات القرعة (السحب لا يحتاج update عادة، إما إنشاء أو حذف/إلغاء)
            'lottery_draw.view', 'lottery_draw.create', 'lottery_draw.delete',

            // الإعدادات
            'setting.view', 'setting.update',
        ];

        // إنشاء الصلاحيات في قاعدة البيانات
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission, 'guard_name' => $guardName]);
        }

        // --- 3. إنشاء الأدوار وتوزيع الصلاحيات بدقة ---

        // أ. دور Super Admin (يملك كل شيء عبر Gate::before فلا داعي لمنحه صلاحيات يدوياً)
        Role::create(['name' => 'Super Admin', 'guard_name' => $guardName]);

        // ب. دور Admin (مدير النظام - يملك كل الصلاحيات المسجلة)
        $adminRole = Role::create(['name' => 'Admin', 'guard_name' => $guardName]);
        $adminRole->givePermissionTo(Permission::all());

        // ج. دور Data Entry (مدخل بيانات / موظف استقبال)
        $dataEntryRole = Role::create(['name' => 'Data Entry', 'guard_name' => $guardName]);

        // نحدد له فقط ما يحتاجه لإدخال بيانات العملاء، ونمنعه من إجراء السحب (lottery_draw.create)
        $dataEntryRole->givePermissionTo([
            'dashboard.view',
            'client.view', 'client.create', 'client.update',
            'lottery_draw.view', // يمكنه رؤية الفائزين فقط
        ]);

        // د. دور Auditor (المراجع) - يرى كل شيء ولا يغير شيئاً
        $auditorRole = Role::create(['name' => 'Auditor', 'guard_name' => $guardName]);

        // المراجع يحصل على أي صلاحية تحتوي على كلمة view فقط
        $viewPermissions = Permission::where('name', 'like', '%.view')->pluck('name');
        $auditorRole->givePermissionTo($viewPermissions);
    }
}
