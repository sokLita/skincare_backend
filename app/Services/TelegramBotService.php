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
                    $this->sendMessage($chatId, "💬 *Contact Support*\n\nPlease type your question or message below, and our support team will get back to you as soon as possible.");
                }
                break;

            case 'back':
                $this->sendMainMenu($chatId, $messageId);
                break;
        }
    }

    /**
     * Show main menu
     */
    protected function sendMainMenu(string $chatId, ?int $messageId = null): void
    {
        $text = "🏠 *Main Menu*\n\nWhat would you like to do?";
        $keyboard = [
            ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
            ['text' => '📂 Categories', 'callback_data' => 'categories'],
            ['text' => '💬 Contact Support', 'callback_data' => 'support'],
        ];

        if ($messageId) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show products with pagination
     */
    protected function showProducts(string $chatId, int $page = 1, ?int $messageId = null): void
    {
        $perPage = 5;
        $products = Product::where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->isEmpty()) {
            $text = "😔 No products available at the moment. Check back later!";
            $keyboard = [['text' => '🏠 Main Menu', 'callback_data' => 'back']];
            if ($messageId) {
                $this->editMessage($chatId, $messageId, $text, $keyboard);
            } else {
                $this->sendMessage($chatId, $text, $keyboard);
            }
            return;
        }

        $text = "🛍️ *Our Products* (Page {$products->currentPage()}/{$products->lastPage()})\n\n";
        foreach ($products as $product) {
            $rating = $product->reviews_avg_rating ? number_format($product->reviews_avg_rating, 1) : 'No reviews';
            $text .= "• *{$product->name}* - \${$product->price}\n";
            $text .= "  ⭐ {$rating}\n\n";
        }

        $keyboard = [];
        $row = [];

        if ($products->previousPageUrl()) {
            $row[] = ['text' => '⬅️ Prev', 'callback_data' => 'products:' . ($page - 1)];
        }
        if ($products->nextPageUrl()) {
            $row[] = ['text' => 'Next ➡️', 'callback_data' => 'products:' . ($page + 1)];
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $keyboard[] = ['text' => '🏠 Main Menu', 'callback_data' => 'back'];

        if ($messageId) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show categories
     */
    protected function showCategories(string $chatId, ?int $messageId = null): void
    {
        $categories = Category::withCount('products')->get();

        if ($categories->isEmpty()) {
            $text = "📂 No categories available.";
            $keyboard = [['text' => '🏠 Main Menu', 'callback_data' => 'back']];
            if ($messageId) {
                $this->editMessage($chatId, $messageId, $text, $keyboard);
            } else {
                $this->sendMessage($chatId, $text, $keyboard);
            }
            return;
        }

        $text = "📂 *Product Categories*\n\nSelect a category to browse products:\n\n";
        $keyboard = [];

        foreach ($categories as $category) {
            $text .= "• {$category->name} ({$category->products_count} products)\n";
            $keyboard[] = [
                ['text' => "{$category->name} ({$category->products_count})", 'callback_data' => "category:{$category->id}"]
            ];
        }

        $keyboard[] = ['text' => '🏠 Main Menu', 'callback_data' => 'back'];

        if ($messageId) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show products by category with pagination
     */
    protected function showProductsByCategory(string $chatId, int $categoryId, int $page = 1, ?int $messageId = null): void
    {
        $category = Category::find($categoryId);
        if (!$category) {
            $this->sendMessage($chatId, "❌ Category not found.");
            return;
        }

        $perPage = 5;
        $products = Product::where('category_id', $categoryId)
            ->where('is_active', true)
            ->withAvg('reviews', 'rating')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);

        if ($products->isEmpty()) {
            $text = "📂 *{$category->name}*\n\nNo products in this category yet.";
            $keyboard = [
                ['text' => '⬅️ Back to Categories', 'callback_data' => 'categories'],
                ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
            ];
            if ($messageId) {
                $this->editMessage($chatId, $messageId, $text, $keyboard);
            } else {
                $this->sendMessage($chatId, $text, $keyboard);
            }
            return;
        }

        $text = "📂 *{$category->name}* (Page {$products->currentPage()}/{$products->lastPage()})\n\n";
        foreach ($products as $product) {
            $rating = $product->reviews_avg_rating ? number_format($product->reviews_avg_rating, 1) : 'No reviews';
            $text .= "• *{$product->name}* - \${$product->price}\n";
            $text .= "  ⭐ {$rating}\n\n";
        }

        $keyboard = [];
        $row = [];

        if ($products->previousPageUrl()) {
            $row[] = ['text' => '⬅️ Prev', 'callback_data' => "category:{$categoryId}:" . ($page - 1)];
        }
        if ($products->nextPageUrl()) {
            $row[] = ['text' => 'Next ➡️', 'callback_data' => "category:{$categoryId}:" . ($page + 1)];
        }
        if (!empty($row)) {
            $keyboard[] = $row;
        }

        $keyboard[] = ['text' => '⬅️ Back to Categories', 'callback_data' => 'categories'];
        $keyboard[] = ['text' => '🏠 Main Menu', 'callback_data' => 'back'];

        if ($messageId) {
            $this->editMessage($chatId, $messageId, $text, $keyboard);
        } else {
            $this->sendMessage($chatId, $text, $keyboard);
        }
    }

    /**
     * Show product detail
     */
    protected function showProductDetail(string $chatId, int $productId, int $messageId): void
    {
        $product = Product::withAvg('reviews', 'rating')->with('category')->find($productId);

        if (!$product) {
            $this->editMessage($chatId, $messageId, "❌ Product not found.", [
                ['text' => '⬅️ Back', 'callback_data' => 'products']
            ]);
            return;
        }

        $rating = $product->reviews_avg_rating ? number_format($product->reviews_avg_rating, 1) . ' ⭐' : 'No reviews yet';
        $text = "🛍️ *{$product->name}*\n\n";
        $text .= "💰 *Price:* \${$product->price}\n";
        $text .= "📂 *Category:* {$product->category->name}\n";
        $text .= "⭐ *Rating:* {$rating}\n";
        if ($product->description) {
            $text .= "\n📝 *Description:*\n{$product->description}\n";
        }

        $keyboard = [
            ['text' => '⬅️ Back to Products', 'callback_data' => 'products'],
            ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
        ];

        $this->editMessage($chatId, $messageId, $text, $keyboard);
    }

    /**
     * Send auto-reply for non-command messages
     */
    protected function sendAutoReply(TelegramUser $telegramUser, string $message, string $chatId): void
    {
        $text = "📩 *Thank you for your message!*\n\nYour message has been forwarded to our support team. We'll get back to you as soon as possible.\n\nIn the meantime, you can:\n\n🛍️ /products - Browse our products\n📂 /categories - View categories\n❓ /help - See all commands";

        $this->sendMessage($chatId, $text, [
            ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
            ['text' => '📂 Categories', 'callback_data' => 'categories'],
            ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
        ]);
    }

    /**
     * Save a message from Telegram user to the chat system
     */
    public function saveTelegramMessage(TelegramUser $telegramUser, string $text): ChatMessage
    {
        return ChatMessage::create([
            'telegram_user_id' => $telegramUser->id,
            'user_id' => $telegramUser->user_id,
            'message' => $text,
            'is_admin' => false,
            'is_read' => false,
            'source' => 'telegram',
        ]);
    }

    /**
     * Send a reply from admin back to Telegram user
     */
    public function sendAdminReply(TelegramUser $telegramUser, string $text): void
    {
        $this->sendMessage($telegramUser->chat_id, "📨 *Reply from Support:*\n\n{$text}");
    }

    /**
     * Register or find a Telegram user
     */
    protected function registerTelegramUser(array $from, string $chatId): TelegramUser
    {
        return TelegramUser::firstOrCreate(
            ['telegram_id' => $from['id']],
            [
                'first_name' => $from['first_name'] ?? null,
                'last_name' => $from['last_name'] ?? null,
                'username' => $from['username'] ?? null,
                'chat_id' => $chatId,
                'language_code' => $from['language_code'] ?? null,
                'is_bot' => $from['is_bot'] ?? false,
            ]
        );
    }

    /**
     * Send a text message with optional inline keyboard
     */
    public function sendMessage(string $chatId, string $text, array $keyboard = []): ?array
    {
        $params = [
            'chat_id' => $chatId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($keyboard)) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => array_map(fn($btn) => is_array($btn[0] ?? null) ? $btn : [$btn], $keyboard),
            ]);
        }

        $response = Http::post("{$this->apiUrl}/sendMessage", $params);

        if (!$response->successful()) {
            Log::error('Telegram sendMessage failed', [
                'response' => $response->body(),
                'chat_id' => $chatId,
            ]);
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Edit an existing message
     */
    protected function editMessage(string $chatId, int $messageId, string $text, array $keyboard = []): ?array
    {
        $params = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
            'parse_mode' => 'Markdown',
        ];

        if (!empty($keyboard)) {
            $params['reply_markup'] = json_encode([
                'inline_keyboard' => array_map(fn($btn) => is_array($btn[0] ?? null) ? $btn : [$btn], $keyboard),
            ]);
        }

        $response = Http::post("{$this->apiUrl}/editMessageText", $params);

        if (!$response->successful()) {
            Log::error('Telegram editMessage failed', [
                'response' => $response->body(),
                'chat_id' => $chatId,
            ]);
        }

        return $response->successful() ? $response->json() : null;
    }

    /**
     * Answer a callback query (remove loading state on button)
     */
    protected function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        Http::post("{$this->apiUrl}/answerCallbackQuery", [
            'callback_query_id' => $callbackQueryId,
            'text' => $text,
        ]);
    }

    /**
     * Set the webhook URL for the bot
     */
    public function setWebhook(string $url): array
    {
        $response = Http::post("{$this->apiUrl}/setWebhook", [
            'url' => $url,
        ]);

        return $response->json();
    }

    /**
     * Remove the webhook
     */
    public function removeWebhook(): array
    {
        $response = Http::post("{$this->apiUrl}/deleteWebhook");

        return $response->json();
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        $response = Http::post("{$this->apiUrl}/getWebhookInfo");

        return $response->json();
    }
}