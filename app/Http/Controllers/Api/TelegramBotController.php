<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TelegramBotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramBotController extends Controller
{
    protected TelegramBotService $botService;

    public function __construct(TelegramBotService $botService)
    {
        $this->botService = $botService;
    }

    /**
     * Webhook endpoint for Telegram to send updates to
     */
    public function webhook(Request $request)
    {
        $update = $request->all();

        Log::info('Telegram webhook received', ['update' => $update]);

        try {
            $this->botService->handleUpdate($update);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Set webhook URL (call this via an authenticated admin route)
     */
    public function setWebhook(Request $request)
    {
        $request->validate([
            'url' => 'required|url',
        ]);

        $result = $this->botService->setWebhook($request->url);

        return response()->json($result);
    }

    /**
     * Remove webhook
     */
    public function removeWebhook()
    {
        $result = $this->botService->removeWebhook();

        return response()->json($result);
    }

    /**
     * Get webhook info
     */
    public function webhookInfo()
    {
        $result = $this->botService->getWebhookInfo();

        return response()->json($result);
    }

    /**
     * Send a message via Telegram bot (admin can use this)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'chat_id' => 'required|string',
            'text' => 'required|string|max:4096',
        ]);

        $result = $this->botService->sendMessage(
            $request->chat_id,
            $request->text
        );

        return response()->json($result);
    }
}