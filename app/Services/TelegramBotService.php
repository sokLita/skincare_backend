<?php

namespace App\Services;

use App\Models\TelegramUser;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramBotService
{
    protected string $token;
    protected string $apiUrl;

    public function __construct()
    {
        $this->token = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
    }

    /**
     * Handle incoming webhook update from Telegram
     */
    public function handleUpdate(array $update): void
    {
        // Handle callback queries (button presses)
        if (isset($update['callback_query'])) {
            $this->handleCallbackQuery($update['callback_query']);
            return;
        }

        // Handle regular messages
        if (isset($update['message'])) {
            $this->handleMessage($update['message']);
        }
    }

    /**
     * Handle regular text messages
     */
    protected function handleMessage(array $message): void
    {
        $chatId = $message['chat']['id'];
        $from = $message['from'];
        $text = $message['text'] ?? '';

        // Register or find the Telegram user
        $telegramUser = $this->registerTelegramUser($from, $chatId);

        // Handle commands
        if (str_starts_with($text, '/')) {
            $this->handleCommand($telegramUser, $text, $chatId);
            return;
        }

        // Save the message from Telegram user to our chat system
        $this->saveTelegramMessage($telegramUser, $text);

        // Send auto-reply for non-command messages
        $this->sendAutoReply($telegramUser, $text, $chatId);
    }

    /**
     * Handle bot commands
     */
    protected function handleCommand(TelegramUser $telegramUser, string $command, string $chatId): void
    {
        $command = strtolower(explode(' ', $command)[0]);

        switch ($command) {
            case '/start':
                $this->sendMessage($chatId, "👋 Welcome to *LiiJune Shop*! 🧴✨\n\nYour go-to destination for premium skincare products.\n\nHere's what I can help you with:\n\n🛍️ /products - Browse our products\n📂 /categories - View categories\n💬 /support - Chat with our team\n❓ /help - See all commands\n\nFeel free to ask me anything about our products!", [
                    ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                    ['text' => '📂 Categories', 'callback_data' => 'categories'],
                    ['text' => '💬 Contact Support', 'callback_data' => 'support'],
                ]);
                break;

            case '/help':
                $this->sendMessage($chatId, "🤖 *Available Commands:*\n\n/start - Start the bot\n/products - Browse our products\n/categories - View product categories\n/support - Chat with our support team\n/help - Show this help message\n\n📞 Need more help? Just type your question!");
                break;

            case '/products':
                $this->showProducts($chatId, 1);
                break;

            case '/categories':
                $this->showCategories($chatId);
                break;

            case '/support':
                $this->sendMessage($chatId, "💬 *Contact Support*\n\nPlease type your question or message below, and our support team will get back to you as soon as possible. We typically respond within 24 hours.\n\n*Or visit our website:* [LiiJune Shop](https://liijune-shop.com)");
                break;

            default:
                $this->sendMessage($chatId, "❌ Unknown command. Type /help to see available commands.");
                break;
        }
    }

    /**
     * Handle callback queries from inline keyboard buttons
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        $chatId = $callbackQuery['message']['chat']['id'];
        $messageId = $callbackQuery['message']['message_id'];
        $data = $callbackQuery['data'];
        $from = $callbackQuery['from'];

        // Answer callback query to remove loading state
        $this->answerCallbackQuery($callbackQuery['id']);

        // Parse callback data
        $parts = explode(':', $data);
        $action = $parts[0];

        switch ($action) {
            case 'products':
                $page = isset($parts[1]) ? (int)$parts[1] : 1;
                $this->showProducts($chatId, $page, $messageId);
                break;

            case 'category':
                $categoryId = $parts[1] ?? null;
                $page = isset($parts[2]) ? (int)$parts[2] : 1;
                if ($categoryId) {
                    $this->showProductsByCategory($chatId, $categoryId, $page, $messageId);
                }
                break;

            case 'categories':
                $this->showCategories($chatId, $messageId);
                break;

            case 'product':
                $productId = $parts[1] ?? null;
                if ($productId) {
                    $this->showProductDetail($chatId, $productId, $messageId);
                }
                break;

            case 'support':
                $telegramUser = TelegramUser::where('chat_id', $chatId)->first();
                if ($telegramUser) {
