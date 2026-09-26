<?php

namespace App\Providers;

use App\Repositories\DepartmentRepository;
use App\Repositories\Interfaces\DepartmentRepositoryInterface;
use App\Repositories\Interfaces\PeriodRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\SemesterRepositoryInterface;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use App\Repositories\Interfaces\UserPermissionRepositoryInterface;
use App\Repositories\Interfaces\UserProfileRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\PeriodRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SemesterRepository;
use App\Repositories\StudentRepository;
use App\Repositories\UserPermissionRepository;
use App\Repositories\UserProfileRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );

        $this->app->bind(
            StudentRepositoryInterface::class,
            StudentRepository::class
        );

        $this->app->bind(
            SemesterRepositoryInterface::class,
            SemesterRepository::class
        );

        $this->app->bind(
            PeriodRepositoryInterface::class,
            PeriodRepository::class
        );

        $this->app->bind(
            UserProfileRepositoryInterface::class,
            UserProfileRepository::class
        );

        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );

        $this->app->bind(
            UserPermissionRepositoryInterface::class,
            UserPermissionRepository::class
        );

        $this->app->bind(
            DepartmentRepositoryInterface::class,
            DepartmentRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production') || filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN)) {
            URL::forceScheme('https');
        }

        // Expire token only if idle/inactive for more than 60 minutes
        Sanctum::authenticateAccessTokensUsing(function ($accessToken, $isValid) {
            if (! $isValid) {
                return false;
            }

            $lastActivity = $accessToken->last_used_at ?? $accessToken->created_at;

            if ($lastActivity && $lastActivity->lt(now()->subMinutes(60))) {
                $accessToken->delete();

                return false;
            }

            return true;
        });
    }
}
