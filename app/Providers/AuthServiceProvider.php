<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\CampaignReport;
use App\Models\Creative;
use App\Models\CreativeComment;
use App\Models\CustomNotification;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\User;
use App\Policies\CampaignReportPolicy;
use App\Policies\CreativeCommentPolicy;
use App\Policies\CreativePolicy;
use App\Policies\InvoicePolicy;
use App\Policies\LeadPolicy;
use App\Policies\NotificationPolicy;
use App\Policies\PackagePolicy;
use App\Policies\SubscriptionPolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\TransactionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Subscription::class => SubscriptionPolicy::class,
        Invoice::class => InvoicePolicy::class,
        Transaction::class => TransactionPolicy::class,
        Creative::class => CreativePolicy::class,
        CreativeComment::class => CreativeCommentPolicy::class,
        Lead::class => LeadPolicy::class,
        SupportTicket::class => SupportTicketPolicy::class,
        CustomNotification::class => NotificationPolicy::class,
        Package::class => PackagePolicy::class,
        CampaignReport::class => CampaignReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Define Gates for general permissions
        
        // Admin-only gates
        Gate::define('access-admin', function (User $user) {
            return $user->isStaff();
        });

        Gate::define('manage-settings', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('manage-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('delete-records', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('impersonate-users', function (User $user) {
            return $user->isAdmin();
        });

        Gate::define('view-activity-logs', function (User $user) {
            return $user->isStaff();
        });

        Gate::define('export-data', function (User $user) {
            return $user->isStaff();
        });

        Gate::define('manage-webhooks', function (User $user) {
            return $user->isAdmin();
        });

        // Client gates
        Gate::define('access-dashboard', function (User $user) {
            return $user->is_active && $user->is_onboarded;
        });

        Gate::define('view-reports', function (User $user) {
            return $user->hasActiveSubscription();
        });

        Gate::define('create-creatives', function (User $user) {
            if (!$user->hasActiveSubscription()) {
                return false;
            }

            $subscription = $user->activeSubscription;
            return $subscription->getCreativesRemainingThisMonth() > 0;
        });

        // Super Admin - bypass all checks
        Gate::before(function (User $user, string $ability) {
            // Optionally, you can have a super admin that bypasses all checks
            // if ($user->is_super_admin) {
            //     return true;
            // }
        });
    }
}