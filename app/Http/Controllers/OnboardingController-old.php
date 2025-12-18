<?php
// app/Http/Controllers/OnboardingController.php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingRequest;
use App\Models\Industry;
use App\Models\Package;
use App\Models\ServiceCategory;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->is_onboarded) {
            return redirect()->route('client.dashboard');
        }

        // Determine current step based on onboarding_data
        $onboardingData = $user->onboarding_data ?? [];
        $currentStep = $this->determineCurrentStep($onboardingData);

        return redirect()->route('onboarding.step', ['step' => $currentStep]);
    }

    public function showStep(int $step)
    {
        $user = auth()->user();

        if ($user->is_onboarded) {
            return redirect()->route('client.dashboard');
        }

        if ($step < 1 || $step > 5) {
            return redirect()->route('onboarding.step', ['step' => 1]);
        }

        $data = ['step' => $step, 'user' => $user];

        switch ($step) {
            case 1:
                $data['industries'] = Industry::active()->ordered()->get();
                break;
            case 3:
                $data['goals'] = [
                    'leads' => 'Generate Leads',
                    'sales' => 'Increase Sales',
                    'brand_awareness' => 'Brand Awareness',
                    'website_traffic' => 'Website Traffic',
                    'app_installs' => 'App Installs',
                ];
                $data['budgets'] = [
                    '10000-25000' => '₹10,000 - ₹25,000',
                    '25000-50000' => '₹25,000 - ₹50,000',
                    '50000-100000' => '₹50,000 - ₹1,00,000',
                    '100000+' => '₹1,00,000+',
                ];
                break;
            case 5:
                $data['serviceCategories'] = ServiceCategory::active()
                    ->with(['packages' => fn($q) => $q->active()->ordered()])
                    ->ordered()
                    ->get();
                break;
        }

        return view("onboarding.step-{$step}", $data);
    }

    public function processStep(OnboardingRequest $request, int $step)
    {
        $user = auth()->user();
        $onboardingData = $user->onboarding_data ?? [];

        switch ($step) {
            case 1:
                $user->update([
                    'company_name' => $request->company_name,
                    'industry_id' => $request->industry_id,
                    'company_website' => $request->company_website,
                ]);
                $onboardingData['step1_completed'] = true;
                break;

            case 2:
                $user->update([
                    'address' => $request->address,
                    'city' => $request->city,
                    'state' => $request->state,
                    'postal_code' => $request->postal_code,
                    'gst_number' => $request->gst_number,
                ]);
                $onboardingData['step2_completed'] = true;
                break;

            case 3:
                $onboardingData['business_goals'] = $request->business_goals;
                $onboardingData['monthly_budget'] = $request->monthly_budget;
                $onboardingData['target_audience'] = $request->target_audience;
                $onboardingData['step3_completed'] = true;
                break;

            case 4:
                $onboardingData['facebook_page_url'] = $request->facebook_page_url;
                $onboardingData['instagram_handle'] = $request->instagram_handle;
                $onboardingData['google_business_url'] = $request->google_business_url;
                $onboardingData['existing_website'] = $request->existing_website;
                $onboardingData['step4_completed'] = true;
                break;
        }

        $user->update(['onboarding_data' => $onboardingData]);

        $nextStep = $step + 1;

        if ($nextStep > 5) {
            return redirect()->route('onboarding.step', ['step' => 5]);
        }

        return redirect()->route('onboarding.step', ['step' => $nextStep]);
    }

    public function complete(Request $request)
    {
        $user = auth()->user();

        $user->update(['is_onboarded' => true]);

        ActivityLogService::log(
            'onboarding_completed',
            'User completed onboarding',
            $user,
            [],
            $user
        );

        // Check if user selected a package
        if ($request->has('package_id')) {
            return redirect()->route('client.subscription.checkout', ['package' => $request->package_id]);
        }

        return redirect()->route('client.dashboard')
            ->with('success', 'Welcome! Your account is now set up.');
    }

    public function skip()
    {
        $user = auth()->user();
        
        $user->update(['is_onboarded' => true]);

        return redirect()->route('client.dashboard')
            ->with('info', 'You can complete your profile later from settings.');
    }

    protected function determineCurrentStep(array $data): int
    {
        if (empty($data['step1_completed'])) return 1;
        if (empty($data['step2_completed'])) return 2;
        if (empty($data['step3_completed'])) return 3;
        if (empty($data['step4_completed'])) return 4;
        return 5;
    }
}