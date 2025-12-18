<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscriptionRequest;
use App\Models\Package;
use App\Models\ServiceCategory;
use App\Models\Subscription;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

class SubscriptionController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware(['auth']);
        // Remove 'onboarding' middleware temporarily if it's causing issues
        // $this->middleware(['auth', 'onboarding']);
        $this->paymentService = $paymentService;
    }

    /**
     * Show current subscription
     */
    public function index()
    {
        $user = auth()->user();
        $subscription = $user->activeSubscription;
        $subscriptionHistory = $user->subscriptions()
            ->with('package.serviceCategory')
            ->latest()
            ->paginate(10);

        return view('client.subscription.index', compact('user', 'subscription', 'subscriptionHistory'));
    }

    /**
     * Show available plans
     */
    public function plans(Request $request)
    {
        $user = auth()->user();

        $categories = ServiceCategory::where('is_active', true)
            ->with(['packages' => function ($q) {
                $q->where('is_active', true)->orderBy('sort_order');
            }])
            ->orderBy('sort_order')
            ->get();

        $selectedCategory = $request->get('category');

        return view('client.subscription.plans', compact('categories', 'selectedCategory', 'user'));
    }

    /**
     * Show checkout page
     */
    public function checkout(Package $package)
    {
        $user = auth()->user();

        if (!$package->is_active) {
            return redirect()->route('client.subscription.plans')
                ->with('error', 'This package is no longer available.');
        }

        return view('client.subscription.checkout', compact('package', 'user'));
    }

    /**
     * Create payment order
     */
    public function createOrder(Request $request)
    {
        // Validate request
        $request->validate([
            'package_id' => 'required|exists:packages,id',
        ]);

        $user = auth()->user();
        $package = Package::findOrFail($request->package_id);

        // Check if package is active
        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This package is no longer available.',
            ], 400);
        }

        try {
            $orderData = $this->paymentService->createSubscriptionOrder($user, $package);

            return response()->json([
                'success' => true,
                'data' => $orderData,
            ]);
        } catch (Exception $e) {
            Log::error('Create order failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'package_id' => $package->id,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order. Please try again.',
            ], 500);
        }
    }

    /**
     * Verify payment and activate subscription
     */
    public function verifyPayment(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
            'package_id' => 'required|exists:packages,id',
        ]);

        try {
            $subscription = $this->paymentService->verifyAndActivateSubscription(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature,
                $request->package_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription activated successfully!',
                'redirect' => route('client.subscription.success', $subscription),
            ]);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            Log::error('Payment signature verification failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed. Please contact support.',
            ], 400);
        } catch (Exception $e) {
            Log::error('Payment verification failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment processing failed. Please contact support.',
            ], 500);
        }
    }

    /**
     * Show success page
     */
    public function success(Subscription $subscription)
    {
        // Check ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $subscription->load('package.serviceCategory');

        return view('client.subscription.success', compact('subscription'));
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request, Subscription $subscription)
    {
        // Check ownership
        if ($subscription->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        return redirect()->route('client.subscription.index')
            ->with('success', 'Subscription cancelled successfully.');
    }
}