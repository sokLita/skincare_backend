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
    protected ?AISkincareService $aiService = null;

    public function __construct(?AISkincareService $aiService = null)
    {
        $this->token = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->token}";
        $this->aiService = $aiService;

        if (empty($this->token)) {
            Log::critical('TELEGRAM_BOT_TOKEN is not configured! Set TELEGRAM_BOT_TOKEN in .env');
        }
    }

    /**
     * Handle incoming webhook update from Telegram
     */
    public function handleUpdate(array $update): void
    {
        try {
            // Handle callback queries (button presses)
            if (isset($update['callback_query'])) {
                $this->handleCallbackQuery($update['callback_query']);
                return;
            }

            // Handle regular messages
            if (isset($update['message'])) {
                $this->handleMessage($update['message']);
                return;
            }

            // Handle channel posts or other update types
            if (isset($update['channel_post'])) {
                Log::info('Telegram channel post received (ignored)', ['chat_id' => $update['channel_post']['chat']['id']]);
                return;
            }

            Log::warning('Telegram update type not handled', ['update_keys' => array_keys($update)]);
        } catch (\Exception $e) {
            Log::error('TelegramBotService@handleUpdate CRITICAL ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Handle regular text messages
     */
    protected function handleMessage(array $message): void
    {
        try {
            $chatId = $message['chat']['id'];
            $from = $message['from'];
            $text = trim($message['text'] ?? '');

            // Log incoming message
            Log::info('Telegram message received', [
                'chat_id' => $chatId,
                'from' => $from['first_name'] ?? 'unknown',
                'username' => $from['username'] ?? 'none',
                'text' => substr($text, 0, 200),
            ]);

            // Register or find the Telegram user
            $telegramUser = $this->registerTelegramUser($from, $chatId);

            // Handle commands
            if (str_starts_with($text, '/')) {
                $this->handleCommand($telegramUser, $text, $chatId);
                return;
            }

            // Save the message from Telegram user to our chat system
            try {
                $this->saveTelegramMessage($telegramUser, $text);
            } catch (\Exception $e) {
                Log::error('Telegram: Failed to save message to DB', [
                    'error' => $e->getMessage(),
                    'chat_id' => $chatId,
                ]);
                // Continue even if DB save fails - still try to reply
            }

            // Send smart auto-reply for non-command messages
            $this->sendAutoReply($telegramUser, $text, $chatId);
        } catch (\Exception $e) {
            Log::error('TelegramBotService@handleMessage ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Try to send fallback error message to user
            try {
                $this->sendMessage((string)($message['chat']['id'] ?? ''), "❌ Sorry, an error occurred while processing your message. Please try again later.");
            } catch (\Exception $e2) {
                Log::error('Telegram: Failed to send fallback error message', ['error' => $e2->getMessage()]);
            }
        }
    }

    /**
     * Handle bot commands
     */
    protected function handleCommand(TelegramUser $telegramUser, string $command, string $chatId): void
    {
        try {
            $command = strtolower(explode(' ', $command)[0]);

            switch ($command) {
                case '/start':
                    $this->sendMessage($chatId, "👋 *Welcome to LiiJune Shop!* 🧴✨\n\nYour go-to destination for premium skincare products.\n\nHere's what I can help you with:\n\n🛍️ /products - Browse our products\n📂 /categories - View categories\n💬 /support - Chat with our team\n❓ /help - See all commands\n\n💡 *You can also ask me skincare questions directly!* Just type your concern and I'll help.", [
                        ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                        ['text' => '📂 Categories', 'callback_data' => 'categories'],
                        ['text' => '💬 Contact Support', 'callback_data' => 'support'],
                        ['text' => '🧴 Skincare Help', 'callback_data' => 'skincare_help'],
                    ]);
                    break;

                case '/help':
                    $this->sendMessage($chatId, "🤖 *Available Commands:*\n\n/start - Start the bot\n/products - Browse our products\n/categories - View product categories\n/support - Chat with our support team\n/skincare - Get skincare advice\n/help - Show this help message\n\n💡 *Tip:* You can also just type any skincare question naturally!\n\n📞 Need more help? Just type your question!");
                    break;

                case '/products':
                case '/shop':
                    $this->showProducts($chatId, 1);
                    break;

                case '/categories':
                    $this->showCategories($chatId);
                    break;

                case '/support':
                    $this->sendMessage($chatId, "💬 *Contact Support*\n\nPlease type your question or message below, and our support team will get back to you as soon as possible. We typically respond within 24 hours.\n\n*Or visit our website:* [LiiJune Shop](https://liijune-shop.com)");
                    break;

                case '/skincare':
                case '/advice':
                    $this->sendMessage($chatId, "🧴 *Skincare Advice*\n\nTell me about your skin concerns and I'll help you out!\n\n*Examples:*\n• I have oily, acne-prone skin\n• What's good for dry skin?\n• How to reduce dark spots?\n• Best routine for aging skin\n\nJust type your question! 😊");
                    break;

                default:
                    $this->sendMessage($chatId, "❌ Unknown command. Type /help to see available commands.");
                    break;
            }
        } catch (\Exception $e) {
            Log::error('TelegramBotService@handleCommand ERROR', [
                'command' => $command,
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            $this->sendMessage($chatId, "❌ Sorry, I couldn't process that command. Please try again.");
        }
    }

    /**
     * Handle callback queries from inline keyboard buttons
     */
    protected function handleCallbackQuery(array $callbackQuery): void
    {
        try {
            $chatId = $callbackQuery['message']['chat']['id'];
            $messageId = $callbackQuery['message']['message_id'];
            $data = $callbackQuery['data'];

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
                    $this->sendMessage($chatId, "💬 *Contact Support*\n\nPlease type your question or message below, and our support team will get back to you as soon as possible.");
                    break;

                case 'skincare_help':
                    $this->sendMessage($chatId, "🧴 *Skincare Assistant*\n\nI can help with:\n\n• Product recommendations ✅\n• Skincare routines ✅\n• Skin concern advice ✅\n• Ingredient information ✅\n\n*Just tell me about your skin!* For example:\n• \"My skin is oily and I get acne\"\n• \"Best moisturizer for dry skin\"\n• \"How to reduce dark spots?\"");
                    break;

                case 'back':
                    $this->sendMainMenu($chatId, $messageId);
                    break;

                default:
                    Log::warning('Telegram: Unknown callback action', ['action' => $action, 'data' => $data]);
                    $this->answerCallbackQuery($callbackQuery['id'], 'Unknown option');
                    break;
            }
        } catch (\Exception $e) {
            Log::error('TelegramBotService@handleCallbackQuery ERROR', [
                'data' => $callbackQuery['data'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
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
            ['text' => '🧴 Skincare Help', 'callback_data' => 'skincare_help'],
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
        try {
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
                $rating = $product->reviews_avg_rating ? number_format($product->reviews_avg_rating, 1) . ' ⭐' : 'No reviews';
                $text .= "• *{$product->name}* - \${$product->price} ({$rating})\n";
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
        } catch (\Exception $e) {
            Log::error('TelegramBotService@showProducts DB ERROR', ['error' => $e->getMessage()]);
            $this->sendMessage($chatId, "❌ Sorry, I couldn't load the products right now. Please try again later.");
        }
    }

    /**
     * Show categories
     */
    protected function showCategories(string $chatId, ?int $messageId = null): void
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('TelegramBotService@showCategories DB ERROR', ['error' => $e->getMessage()]);
            $this->sendMessage($chatId, "❌ Sorry, I couldn't load the categories right now.");
        }
    }

    /**
     * Show products by category with pagination
     */
    protected function showProductsByCategory(string $chatId, int $categoryId, int $page = 1, ?int $messageId = null): void
    {
        try {
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
                $rating = $product->reviews_avg_rating ? number_format($product->reviews_avg_rating, 1) . ' ⭐' : 'No reviews';
                $text .= "• *{$product->name}* - \${$product->price} ({$rating})\n";
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
        } catch (\Exception $e) {
            Log::error('TelegramBotService@showProductsByCategory DB ERROR', ['error' => $e->getMessage()]);
            $this->sendMessage($chatId, "❌ Sorry, I couldn't load the products for this category.");
        }
    }

    /**
     * Show product detail
     */
    protected function showProductDetail(string $chatId, int $productId, int $messageId): void
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('TelegramBotService@showProductDetail DB ERROR', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Smart auto-reply using AI skincare service for non-command messages
     */
    protected function sendAutoReply(TelegramUser $telegramUser, string $message, string $chatId): void
    {
        try {
            $lowerMessage = strtolower(trim($message));

            // === DETECT MESSAGE INTENT ===

            // Hi / Hello / Hey (greetings)
            if (preg_match('/^(hi|hello|hey|greetings|good\s*(morning|afternoon|evening)|yo|sup|howdy)\b/i', trim($message))) {
                $this->sendMessage($chatId, "👋 *Hello!* Welcome to *LiiJune Shop*! 🧴✨\n\nHow can I help you today?\n\n💡 You can ask me about:\n• Skincare products and recommendations\n• Skincare routines and advice\n• Our product categories\n\nOr just tell me about your skin concerns!", [
                    ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                    ['text' => '🧴 Skincare Help', 'callback_data' => 'skincare_help'],
                    ['text' => '📂 Categories', 'callback_data' => 'categories'],
                ]);
                return;
            }

            // Thank you messages
            if (preg_match('/\b(thanks?|thank\s*you|appreciate|grateful)\b/i', $message)) {
                $this->sendMessage($chatId, "😊 *You're very welcome!* I'm happy to help.\n\nIf you have any other questions, feel free to ask!\n\n💡 You can also /start to see the main menu.", [
                    ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                    ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
                ]);
                return;
            }

            // Bye messages
            if (preg_match('/\b(bye|goodbye|see\s*you|farewell|talk\s*to\s*you\s*later)\b/i', $message)) {
                $this->sendMessage($chatId, "👋 *Goodbye!* Thanks for chatting with us!\n\nIf you need anything, just send a message. Have a great day! 😊", [
                    ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                    ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
                ]);
                return;
            }

            // === TRY AI SKINCARE SERVICE ===
            if ($this->aiService) {
                try {
                    $startTime = microtime(true);
                    $aiResponse = $this->aiService->processMessage($message, []);
                    $elapsed = (microtime(true) - $startTime) * 1000;

                    Log::info('Telegram AI response generated', [
                        'chat_id' => $chatId,
                        'response_time_ms' => round($elapsed, 0),
                    ]);

                    // Extract and send the AI response
                    $responseText = $aiResponse['response'] ?? '';

                    // If response was successfully generated and seems meaningful
                    if (!empty($responseText) && strlen($responseText) > 20) {
                        // Build suggestions as buttons if available
                        $suggestions = $aiResponse['suggestions'] ?? [];
                        $keyboard = [];

                        // Add AI-based action buttons
                        if (($aiResponse['ask_seller'] ?? false)) {
                            $keyboard[] = ['text' => '💬 Talk to Consultant', 'callback_data' => 'support'];
                        }

                        // Add some common navigation buttons
                        $keyboard[] = ['text' => '🛍️ Products', 'callback_data' => 'products'];
                        $keyboard[] = ['text' => '🏠 Main Menu', 'callback_data' => 'back'];

                        // Truncate very long responses for Telegram (max 4096 chars)
                        if (strlen($responseText) > 4000) {
                            $responseText = substr($responseText, 0, 3990) . "...\n\n*Message too long, visit our website for full details.*";
                        }

                        $this->sendMessage($chatId, $responseText, $keyboard);

                        // Also send suggestion chips as a separate message if available
                        if (!empty($suggestions)) {
                            $suggestionText = "💡 *You can also ask:*\n" . implode("\n", array_map(fn($s) => "• {$s}", array_slice($suggestions, 0, 4)));
                            $this->sendMessage($chatId, $suggestionText);
                        }

                        return;
                    }
                } catch (\Exception $e) {
                    Log::error('Telegram AI service error', [
                        'chat_id' => $chatId,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Fall through to fallback response
                }
            }

            // === FALLBACK: If no AI response or AI not available ===
            // Detect skincare-related keywords for contextual fallback
            $skincareKeywords = ['skin', 'acne', 'pimple', 'dry', 'oily', 'moisturizer', 'cream', 'serum', 'face',
                'cleanser', 'wash', 'routine', 'scars', 'dark spot', 'wrinkle', 'aging', 'sensitive',
                'redness', 'blackhead', 'whitehead', 'pore', 'glow', 'bright', 'tan', 'sunblock',
                'spf', 'sunscreen', 'vitamin', 'retinol', 'niacinamide', 'hyaluronic', 'blemish'];

            $hasSkincareKeywords = false;
            foreach ($skincareKeywords as $keyword) {
                if (str_contains($lowerMessage, $keyword)) {
                    $hasSkincareKeywords = true;
                    break;
                }
            }

            if ($hasSkincareKeywords) {
                $this->sendMessage($chatId, "🧴 *Skincare Help*\n\nI understand you're asking about skincare! Here are some things I can help with:\n\n• *Product recommendations* - Just ask \"What products for acne?\"\n• *Skincare routines* - Ask \"What's a good routine?\"\n• *Skin concerns* - Tell me your issue\n\n📌 *Tip:* Try /products to browse our products or /categories to see categories!", [
                    ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                    ['text' => '📂 Categories', 'callback_data' => 'categories'],
                    ['text' => '💬 Contact Support', 'callback_data' => 'support'],
                ]);
                return;
            }

            // Final fallback - always respond
            $this->sendMessage($chatId, "😊 *Thanks for your message!*\n\nI'm not sure I understood your request. Here are some things I can help with:\n\n🛍️ /products - Browse our products\n📂 /categories - View categories\n🧴 *Ask about skincare* - Tell me your skin concern\n💬 /support - Chat with our team\n❓ /help - See all commands\n\n*Or just tell me about your skin!*", [
                ['text' => '🛍️ Browse Products', 'callback_data' => 'products'],
                ['text' => '📂 Categories', 'callback_data' => 'categories'],
                ['text' => '🧴 Skincare Help', 'callback_data' => 'skincare_help'],
                ['text' => '🏠 Main Menu', 'callback_data' => 'back'],
            ]);

        } catch (\Exception $e) {
            Log::error('TelegramBotService@sendAutoReply ERROR', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Ultimate fallback - ensure user always gets a response
            try {
                $this->sendMessage($chatId, "🙏 *Thanks for reaching out!*\n\nI'm having a bit of trouble processing your request. Please try again or use /help to see available commands.");
            } catch (\Exception $e2) {
                Log::error('Telegram: Failed to send ultimate fallback', ['error' => $e2->getMessage()]);
            }
        }
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
        try {
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
        } catch (\Exception $e) {
            Log::error('Telegram: Failed to register user', [
                'telegram_id' => $from['id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Send a text message with optional inline keyboard
     */
    public function sendMessage(string $chatId, string $text, array $keyboard = []): ?array
    {
        // Validate token before making API call
        if (empty($this->token) || $this->token === 'YOUR_BOT_TOKEN_HERE') {
            Log::error('Telegram: Cannot send message - BOT TOKEN NOT CONFIGURED! Set TELEGRAM_BOT_TOKEN in .env');
            return null;
        }

        // Validate chat_id
        if (empty($chatId)) {
            Log::error('Telegram: Cannot send message - empty chat_id');
            return null;
        }

        try {
            $params = [
                'chat_id' => $chatId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ];

            if (!empty($keyboard)) {
                // Ensure proper keyboard formatting for Telegram API
                $inlineKeyboard = [];
                foreach ($keyboard as $btn) {
                    if (isset($btn['text'])) {
                        // Single button
                        $inlineKeyboard[] = [$btn];
                    } elseif (is_array($btn)) {
                        // Row of buttons or single button in array
                        $row = [];
                        foreach ($btn as $subBtn) {
                            if (is_array($subBtn) && isset($subBtn['text'])) {
                                $row[] = $subBtn;
                            } elseif (is_string($btn)) {
                                // Fallback - shouldn't happen with our new format
                            }
                        }
                        if (!empty($row)) {
                            $inlineKeyboard[] = $row;
                        } else if (isset($btn['text'])) {
                            $inlineKeyboard[] = [$btn];
                        }
                    }
                }

                if (!empty($inlineKeyboard)) {
                    $params['reply_markup'] = json_encode([
                        'inline_keyboard' => $inlineKeyboard,
                    ]);
                }
            }

            $startTime = microtime(true);
            $response = Http::timeout(15)->post("{$this->apiUrl}/sendMessage", $params);
            $elapsed = (microtime(true) - $startTime) * 1000;

            if (!$response->successful()) {
                $responseBody = $response->json();
                $errorDescription = $responseBody['description'] ?? 'Unknown error';
                $errorCode = $responseBody['error_code'] ?? 0;

                Log::error('Telegram sendMessage FAILED', [
                    'response' => $responseBody,
                    'chat_id' => $chatId,
                    'error_code' => $errorCode,
                    'description' => $errorDescription,
                    'response_time_ms' => round($elapsed, 0),
                ]);

                // If bot was blocked or chat not found, log it
                if ($errorCode === 403) {
                    Log::warning("Telegram: Bot was blocked by user {$chatId}");
                } elseif ($errorCode === 400 && str_contains($errorDescription, 'chat not found')) {
                    Log::warning("Telegram: Chat not found for {$chatId}");
                }

                return null;
            }

            Log::info('Telegram message sent successfully', [
                'chat_id' => $chatId,
                'response_time_ms' => round($elapsed, 0),
                'text_length' => strlen($text),
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram sendMessage EXCEPTION', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Edit an existing message
     */
    protected function editMessage(string $chatId, int $messageId, string $text, array $keyboard = []): ?array
    {
        if (empty($this->token)) {
            Log::error('Telegram: Cannot edit message - BOT TOKEN NOT CONFIGURED');
            return null;
        }

        try {
            $params = [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'Markdown',
            ];

            if (!empty($keyboard)) {
                $inlineKeyboard = [];
                foreach ($keyboard as $btn) {
                    if (isset($btn['text'])) {
                        $inlineKeyboard[] = [$btn];
                    } elseif (is_array($btn)) {
                        $row = [];
                        foreach ($btn as $subBtn) {
                            if (is_array($subBtn) && isset($subBtn['text'])) {
                                $row[] = $subBtn;
                            }
                        }
                        if (!empty($row)) {
                            $inlineKeyboard[] = $row;
                        } elseif (isset($btn['text'])) {
                            $inlineKeyboard[] = [$btn];
                        }
                    }
                }
                if (!empty($inlineKeyboard)) {
                    $params['reply_markup'] = json_encode([
                        'inline_keyboard' => $inlineKeyboard,
                    ]);
                }
            }

            $response = Http::timeout(15)->post("{$this->apiUrl}/editMessageText", $params);

            if (!$response->successful()) {
                $responseBody = $response->json();
                Log::error('Telegram editMessage failed', [
                    'response' => $responseBody,
                    'chat_id' => $chatId,
                    'message_id' => $messageId,
                ]);
            }

            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('Telegram editMessage EXCEPTION', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Answer a callback query (remove loading state on button)
     */
    protected function answerCallbackQuery(string $callbackQueryId, string $text = ''): void
    {
        try {
            if (empty($this->token)) return;

            Http::timeout(10)->post("{$this->apiUrl}/answerCallbackQuery", [
                'callback_query_id' => $callbackQueryId,
                'text' => $text,
            ]);
        } catch (\Exception $e) {
            // Silently fail for callback queries
            Log::debug('Telegram answerCallbackQuery failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Set the webhook URL for the bot
     */
    public function setWebhook(string $url): array
    {
        try {
            if (empty($this->token)) {
                return ['ok' => false, 'description' => 'Bot token not configured'];
            }

            $response = Http::timeout(15)->post("{$this->apiUrl}/setWebhook", [
                'url' => $url,
                'allowed_updates' => json_encode(['message', 'callback_query', 'channel_post']),
            ]);

            $result = $response->json();
            Log::info('Telegram setWebhook result', ['result' => $result]);
            return $result;
        } catch (\Exception $e) {
            Log::error('Telegram setWebhook exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Remove the webhook
     */
    public function removeWebhook(): array
    {
        try {
            if (empty($this->token)) {
                return ['ok' => false, 'description' => 'Bot token not configured'];
            }

            $response = Http::timeout(15)->post("{$this->apiUrl}/deleteWebhook", [
                'drop_pending_updates' => true,
            ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram removeWebhook exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }

    /**
     * Get webhook info
     */
    public function getWebhookInfo(): array
    {
        try {
            if (empty($this->token)) {
                return ['ok' => false, 'description' => 'Bot token not configured'];
            }

            $response = Http::timeout(15)->post("{$this->apiUrl}/getWebhookInfo");
            return $response->json();
        } catch (\Exception $e) {
            Log::error('Telegram getWebhookInfo exception', ['error' => $e->getMessage()]);
            return ['ok' => false, 'description' => $e->getMessage()];
        }
    }
}