<?php

namespace App\Http\Controllers;

use App\Models\Industry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    protected int $totalSteps = 5;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /*------------------------------------------------------
     | Detect which step user should continue from
     ------------------------------------------------------*/
    protected function determineCurrentStep($data): int
    {
        return !empty($data['step1']) ? (
            !empty($data['step2']) ? (
                !empty($data['step3']) ? (
                    !empty($data['step4']) ? 5 : 4
                ) : 3
            ) : 2
        ) : 1;
    }

    /*------------------------------------------------------
     | Redirect to the correct step
     ------------------------------------------------------*/
    public function index()
    {
        $user = auth()->user();

        if ($user->is_onboarded) {
            return redirect()->route('client.dashboard');
        }

        $onboardingData = $user->onboarding_data ?? [];
        $currentStep = $this->determineCurrentStep($onboardingData);

        return redirect()->route('onboarding.step', ['step' => $currentStep]);
    }

    /*------------------------------------------------------
     | Show step view
     ------------------------------------------------------*/
    public function show($step = 1)
    {
        $user = Auth::user();
        $step = (int) $step;

        if ($user->is_onboarded) {
            return redirect()->route('client.dashboard');
        }

        $step = max(1, min($step, $this->totalSteps));

        $data = [
            'user' => $user,
            'step' => $step
        ];

        if ($step === 1) {
            $data['industries'] = Industry::orderBy('name')->get();
        }

        return view("onboarding.step-{$step}", $data);
    }

    /*------------------------------------------------------
     | Process each step
     ------------------------------------------------------*/
    public function process(Request $request, $step)
    {
        $user = Auth::user();
        $step = (int) $step;

        switch ($step) {
            case 1:
                $validated = $request->validate([
                    'company_name' => 'required|string|max:255',
                    'industry_id' => 'required|exists:industries,id',
                    'company_website' => 'nullable|url|max:255',
                ]);
                $user->update($validated);
                break;

            case 2:
                $validated = $request->validate([
                    'phone' => 'required|string|max:20',
                    'alt_phone' => 'nullable|string|max:20',
                    'city' => 'required|string|max:100',
                    'state' => 'required|string|max:100',
                ]);
                $user->update($validated);
                break;

            case 3:
                $validated = $request->validate([
                    'monthly_budget' => 'required',
                    'team_size' => 'required|string|max:20',
                ]);
                $user->update($validated);
                break;

            case 4:
                $validated = $request->validate([
                    'platforms' => 'required|array|min:1',
                    'platforms.*' => 'string',
                    'primary_goal' => 'nullable|string|max:50',
                ]);

                $user->update([
                    'platforms' => $validated['platforms'],
                    'primary_goal' => $validated['primary_goal'] ?? null,
                ]);
                break;

            case 5:
                $request->validate([
                    'terms' => 'required|accepted',
                    'marketing_consent' => 'nullable',
                ]);

                $user->update([
                    'is_onboarded' => true,
                    'onboarded_at' => now(),
                    'marketing_consent' => $request->boolean('marketing_consent'),
                ]);

                return redirect()->route('onboarding.complete');
        }

        $nextStep = min($step + 1, $this->totalSteps);

        return redirect()->route('onboarding.step', ['step' => $nextStep]);
    }

    /*------------------------------------------------------
     | Completion page
     ------------------------------------------------------*/
    public function complete()
    {
        $user = Auth::user();

        if (!$user->is_onboarded) {
            return redirect()->route('onboarding.step', ['step' => 1]);
        }

        return view('onboarding.complete', ['user' => $user]);
    }

    /*------------------------------------------------------
     | Skip onboarding
     ------------------------------------------------------*/
    public function skip()
    {
        $user = Auth::user();
        $user->update([
            'is_onboarded' => true,
            'onboarded_at' => now(),
        ]);

        return redirect()->route('client.dashboard')
            ->with('info', 'You can complete your profile later.');
    }
}
