<?php

namespace App\Providers;

// النماذج (Models)
use App\Models\User;
use App\Models\Client;
use App\Models\LotteryDraw;
use Spatie\Permission\Models\Role;

// السياسات (Policies)
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;
use App\Policies\ClientPolicy;
use App\Policies\LotteryPolicy;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * رسم خارطة النماذج والسياسات الخاصة بها.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // إدارة المستخدمين والأدوار
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,

        // نظام العملاء والقرعة
        Client::class => ClientPolicy::class,
        LotteryDraw::class => LotteryPolicy::class,
    ];

    /**
     * تسجيل أي خدمات مصادقة أو تصريح.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // منح الـ Super Admin صلاحية الوصول الكامل لكل شيء دون فحص الصلاحيات
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Super Admin') ? true : null;
        });
    }
}
