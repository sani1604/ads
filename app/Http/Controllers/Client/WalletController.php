<?php
// app/Http/Controllers/Client/WalletController.php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\WalletRechargeRequest;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->middleware(['auth', 'onboarding']);
        $this->paymentService = $paymentService;
    }

    /**
     * Show wallet overview
     */
    public function index()
    {
        $user = auth()->user();
        
        $walletTransactions = $user->walletTransactions()
            ->latest()
            ->paginate(15);

        $recentTransactions = $user->transactions()
            ->completed()
            ->latest()
            ->take(5)
            ->get();

        return view('client.wallet.index', compact('user', 'walletTransactions', 'recentTransactions'));
    }

    /**
     * Show recharge page
     */
    public function recharge()
    {
        $user = auth()->user();
        
        $suggestedAmounts = [5000, 10000, 25000, 50000, 100000];

        return view('client.wallet.recharge', compact('user', 'suggestedAmounts'));
    }

    /**
     * Create recharge order
     */
    public function createRechargeOrder(WalletRechargeRequest $request)
    {
        $user = auth()->user();

        try {
            $orderData = $this->paymentService->createWalletRechargeOrder(
                $user,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'data' => $orderData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify recharge payment
     */
    public function verifyRecharge(Request $request)
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        try {
            $transaction = $this->paymentService->verifyAndCreditWallet(
                $request->razorpay_order_id,
                $request->razorpay_payment_id,
                $request->razorpay_signature
            );

            return response()->json([
                'success' => true,
                'message' => 'Wallet recharged successfully!',
                'new_balance' => auth()->user()->fresh()->formatted_wallet_balance,
                'redirect' => route('client.wallet.index'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}