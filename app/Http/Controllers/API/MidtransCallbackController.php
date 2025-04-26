<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\MidtransService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;

class MidtransCallbackController extends Controller
{
    protected $midtransService;

    public function __construct(MidtransService $midtransService)
    {
        $this->midtransService = $midtransService;
    }

    /**
     * Handle callback dari Midtrans
     */
    public function handle(Request $request)
    {
        // Log raw webhook data
        Log::info('Midtrans webhook received', $request->all());

        try {
            // Log the order ID separately for easier search
            $orderId = $request->input('order_id', 'unknown');
            $transactionStatus = $request->input('transaction_status', 'unknown');

            Log::info("Processing webhook for order {$orderId} with status {$transactionStatus}");

            $this->midtransService->processNotification($request->all());

            Log::info("Webhook processing completed for order {$orderId}");

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Webhook processing error: ' . $e->getMessage(), [
                'order_id' => $request->input('order_id', 'unknown'),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);

            // Still return 200 to prevent Midtrans from retrying
            return response()->json(['status' => 'success', 'error' => $e->getMessage()]);
        }
    }

    /**
     * Generate token untuk pembayaran mobile
     */
    public function getToken(Request $request)
    {
        try {
            // Validasi request dasar
            $request->validate([
                'order_id' => 'required|string',
                'gross_amount' => 'required|numeric',
                'customer_details' => 'required|array',
                'item_details' => 'required|array'
            ]);

            // Gunakan service yang sudah diinjeksi
            $result = $this->midtransService->getToken($request->all());

            // Return response sesuai hasil dari service
            if ($result['success']) {
                // Jika order_id mengandung pattern untuk guest order, pastikan ada record pembayaran
                if ($result['success'] && strpos($request->order_id, 'SIAYAM-GUEST-') === 0) {
                    $this->midtransService->ensureGuestPaymentRecord($request->all(), $result['data']);
                }

                return response()->json([
                    'success' => true,
                    'data' => $result['data']
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error generating token: ' . $e->getMessage(), [
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
