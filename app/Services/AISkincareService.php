<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class AISkincareService
{
    private array $skinConcerns = [
        'oily' => ['oily skin', 'greasy skin', 'shiny skin', 'excess oil', 'large pores'],
        'acne' => ['acne', 'pimples', 'zits', 'breakouts', 'spots', 'blemishes', 'cystic acne'],
        'dry' => ['dry skin', 'flaky skin', 'rough skin', 'tight skin', 'dehydrated'],
        'sensitive' => ['sensitive skin', 'irritation', 'redness', 'itching', 'stinging', 'burning'],
        'aging' => ['wrinkles', 'fine lines', 'aging', 'mature skin', 'crow\'s feet', 'sagging'],
        'dark_spots' => ['dark spots', 'hyperpigmentation', 'melasma', 'uneven skin tone', 'discoloration'],
        'dullness' => ['dull skin', 'lackluster', 'tired skin', 'uneven texture', 'rough texture'],
        'blackheads' => ['blackheads', 'clogged pores', 'whiteheads', 'sebaceous filaments'],
        'under_eye' => ['dark circles', 'under eye bags', 'puffy eyes', 'eye bags'],
        'redness' => ['redness', 'rosacea', 'inflamed skin', 'inflammation'],
    ];

    private array $ageRanges = [
        'teens' => ['teen', 'teenager', '13', '14', '15', '16', '17', '18', '19'],
        'twenties' => ['20', '21', '22', '23', '24', '25', '26', '27', '28', '29'],
        'thirties' => ['30', '31', '32', '33', '34', '35', '36', '37', '38', '39'],
        'forties' => ['40', '41', '42', '43', '44', '45', '46', '47', '48', '49'],
        'fifties' => ['50', '51', '52', '53', '54', '55', '56', '57', '58', '59'],
        'sixty_plus' => ['60', '61', '62', '63', '64', '65', '66', '67', '68', '69', '70'],
    ];

    private array $routineSteps = [
        'cleanser' => [
            'name' => 'Gentle Cleanser',
            'description' => 'A mild, pH-balanced cleanser that removes impurities without stripping the skin.',
            'benefits' => 'Removes dirt, oil, and makeup while maintaining skin barrier',
        ],
        'toner' => [
            'name' => 'Toner',
            'description' => 'Balances skin pH and preps skin for better absorption of subsequent products.',
            'benefits' => 'Tightens pores, refreshes skin, and removes residual impurities',
        ],
        'serum' => [
            'name' => 'Treatment Serum',
            'description' => 'Concentrated formula targeting specific skin concerns.',
            'benefits' => 'Delivers active ingredients deep into the skin',
        ],
        'moisturizer' => [
            'name' => 'Moisturizer',
            'description' => 'Hydrates and seals moisture into the skin.',
            'benefits' => 'Prevents water loss, keeps skin soft and supple',
        ],
        'sunscreen' => [
            'name' => 'Sunscreen SPF 50',
            'description' => 'Broad-spectrum sun protection essential for all skin types.',
            'benefits' => 'Protects against UV damage, prevents premature aging and dark spots',
        ],
    ];

    private string $disclaimer = "\n\n*Disclaimer: This information is for educational purposes only and is not a substitute for professional medical advice. For serious skin conditions, please consult a dermatologist.*";

    private string $greeting = "✨ Welcome to Skincare Assistant! ✨\n\nI'm here to help you with personalized skincare advice. Please tell me about your skin concerns, and I'll provide recommendations tailored just for you.\n\n💡 You can ask me about:\n• Product recommendations for your skin type\n• Skincare routines\n• Solutions for specific concerns (acne, dryness, aging, etc.)\n• Ingredient information\n\n🤔 What's your main skin concern?";

    public function processMessage(string $message, array $conversationData = []): array
    {
        $message = trim($message);
        $lowerMessage = strtolower($message);

        // Check if asking about specific products from shop
        if ($this->isAskingProducts($lowerMessage)) {
            return $this->getProductRecommendations($lowerMessage, $conversationData);
        }

        // Check if greeting
        if ($this->isGreeting($lowerMessage)) {
            return [
                'response' => $this->greeting,
                'suggestions' => [
                    "I have oily skin and acne",
                    "What's good for dry skin?",
                    "How do I reduce dark spots?",
                    "Best moisturizer for sensitive skin",
                ],
                'ask_seller' => false,
            ];
        }

        // Check if asking about routine
        if ($this->isAskingRoutine($lowerMessage)) {
            return $this->buildRoutineResponse($conversationData);
        }

        // Analyze skin concerns
        $concerns = $this->analyzeConcerns($lowerMessage);
        $ageInfo = $this->extractAgeInfo($lowerMessage, $conversationData);

        if (empty($concerns)) {
            // Need more info - ask follow-up questions
            return $this->askFollowUp($lowerMessage, $conversationData);
        }

        // Generate detailed response
        return $this->generateResponse($concerns, $ageInfo, $lowerMessage, $conversationData);
    }

    private function isAskingProducts(string $message): bool
    {
        $keywords = ['product', 'recommend', 'suggest', 'best', 'buy', 'available', 'price', 'what should i use', 'which product'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function isGreeting(string $message): bool
    {
        $greetings = ['hi', 'hello', 'hey', 'good morning', 'good afternoon', 'good evening', 'start', 'begin', 'help'];
        foreach ($greetings as $greeting) {
            if (str_contains($message, $greeting)) {
                return true;
            }
        }
        return false;
    }

    private function isAskingRoutine(string $message): bool
    {
        $keywords = ['routine', 'regimen', 'steps', 'order', 'morning routine', 'night routine', 'daily routine'];
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }
        return false;
    }

    private function analyzeConcerns(string $message): array
    {
        $concerns = [];
        foreach ($this->skinConcerns as $key => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    $concerns[$key] = true;
                    break;
                }
            }
        }
        return array_keys($concerns);
    }

    private function extractAgeInfo(string $message, array $conversationData): ?string
    {
        // Check current message
        foreach ($this->ageRanges as $range => $values) {
            foreach ($values as $value) {
                if (str_contains($message, $value)) {
                    return $range;
                }
            }
        }

        // Check conversation history
        if (!empty($conversationData)) {
            foreach ($conversationData as $entry) {
                if (is_array($entry) && isset($entry['message'])) {
                    $msg = strtolower($entry['message']);
                    foreach ($this->ageRanges as $range => $values) {
                        foreach ($values as $value) {
                            if (str_contains($msg, $value)) {
                                return $range;
                            }
                        }
                    }
                }
            }
        }

        return null;
    }

    private function extractProductUsage(string $message): ?string
    {
        $patterns = [
            'cleanser' => ['cleanser', 'face wash', 'facial cleanser', 'wash face'],
            'moisturizer' => ['moisturizer', 'moisturiser', 'lotion', 'cream'],
            'serum' => ['serum', 'vitamin c', 'vitamin c serum'],
            'sunscreen' => ['sunscreen', 'sunblock', 'spf', 'sun protection'],
            'toner' => ['toner', 'toning', 'astringent'],
        ];

        foreach ($patterns as $product => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($message, $keyword)) {
                    return $product;
                }
            }
        }

        return null;
    }

    private function askFollowUp(string $message, array $conversationData): array
    {
        // Check if we already asked for age
        $ageAsked = false;
        $concernAsked = false;
        $productUsageAsked = false;

        if (!empty($conversationData)) {
            foreach ($conversationData as $entry) {
                if (is_array($entry) && isset($entry['type'])) {
                    if ($entry['type'] === 'ai' && str_contains($entry['message'] ?? '', 'age')) {
                        $ageAsked = true;
                    }
                    if ($entry['type'] === 'ai' && str_contains($entry['message'] ?? '', 'concern')) {
                        $concernAsked = true;
                    }
                }
            }
        }

        $ageInfo = $this->extractAgeInfo($message, $conversationData);
        $productUsage = $this->extractProductUsage($message);
        $concerns = $this->analyzeConcerns($message);

        if (empty($concerns) && !$concernAsked) {
            return [
                'response' => "🤔 I'd love to help you with your skincare! Could you tell me more about your skin?\n\n* What specific skin concerns do you have? (e.g., acne, dryness, oiliness, dark spots)\n* What is your skin type? (oily, dry, combination, sensitive, normal)\n\nThis will help me provide the best recommendations for you! 💫",
                'suggestions' => [
                    "My skin is oily and I get acne",
                    "I have dry, sensitive skin",
                    "I'm concerned about aging and wrinkles",
                    "I have dark spots and uneven skin tone",
                ],
                'ask_seller' => false,
            ];
        }

        if (!$ageInfo && !$ageAsked && count($concerns) > 0) {
            return [
                'response' => "Thank you for sharing! To give you more personalized recommendations, could you tell me your **age range**? This helps me tailor the advice to your skin's needs at your stage of life. 🌸",
                'suggestions' => [
                    "I'm in my teens",
                    "I'm in my 20s",
                    "I'm in my 30s",
                    "I'm in my 40s or older",
                ],
                'ask_seller' => false,
            ];
        }

        if (!$productUsage && !$productUsageAsked) {
            return [
                'response' => "Great, I'm getting a better picture! One more thing — what skincare products are you currently using? This helps me know what to add or change. 🧴",
                'suggestions' => [
                    "Just a cleanser",
                    "Cleanser and moisturizer",
                    "A full routine",
                    "Nothing yet, I'm new to skincare",
                ],
                'ask_seller' => false,
            ];
        }

        // If we have enough info, generate response
        return $this->generateResponse($concerns, $ageInfo, $message, $conversationData);
    }

    private function getProductRecommendations(string $message, array $conversationData): array
    {
        $concerns = $this->analyzeConcerns($message);
        
        if (empty($concerns)) {
            // Try to find concerns from conversation history
            if (!empty($conversationData)) {
                foreach ($conversationData as $entry) {
                    if (is_array($entry) && isset($entry['message'])) {
                        $entryConcerns = $this->analyzeConcerns(strtolower($entry['message']));
                        $concerns = array_merge($concerns, $entryConcerns);
                    }
                }
            }
        }

        // Get actual products from the database
        $products = $this->fetchRecommendedProducts($concerns);
        
        $response = "🌟 **Product Recommendations** 🌟\n\nBased on your skin concerns, here are some products available in our shop that I recommend:\n\n";
        
        $productSuggestions = [];
        if ($products->isNotEmpty()) {
            foreach ($products as $product) {
                $response .= "• **{$product->name}** - \${$product->price}\n";
                if ($product->description) {
                    $response .= "  {$product->description}\n";
                }
                $response .= "\n";
                $productSuggestions[] = "View {$product->name}";
            }
        }

        $response .= "💡 **General Skincare Tips:**\n";
        
        if (in_array('oily', $concerns) || in_array('acne', $concerns)) {
            $response .= "• Use oil-free, non-comedogenic products\n";
            $response .= "• Cleanse twice daily with a gentle cleanser\n";
            $response .= "• Look for ingredients like salicylic acid, niacinamide, and tea tree oil\n";
        }
        if (in_array('dry', $concerns)) {
            $response .= "• Use hydrating ingredients like hyaluronic acid, glycerin, and ceramides\n";
            $response .= "• Apply moisturizer while skin is still damp\n";
            $response .= "• Avoid harsh sulfates in cleansers\n";
        }
        if (in_array('aging', $concerns)) {
            $response .= "• Incorporate retinol or peptides into your night routine\n";
            $response .= "• Always wear SPF 50+ daily\n";
            $response .= "• Vitamin C serum in the morning for antioxidant protection\n";
        }
        if (in_array('sensitive', $concerns)) {
            $response .= "• Choose fragrance-free and alcohol-free products\n";
            $response .= "• Patch test new products before full application\n";
            $response .= "• Look for soothing ingredients like centella asiatica and oatmeal\n";
        }
        if (in_array('dark_spots', $concerns)) {
            $response .= "• Vitamin C, niacinamide, and kojic acid help brighten dark spots\n";
            $response .= "• SUNSCREEN is essential to prevent further darkening\n";
            $response .= "• Be patient — it can take 8-12 weeks to see results\n";
        }

        $response .= "\n🛍️ Would you like me to show you more specific products, or would you like to speak with one of our skincare consultants?";
        $response .= $this->disclaimer;

        return [
            'response' => $response,
            'suggestions' => $productSuggestions,
            'ask_seller' => true,
            'products' => $products->pluck('id')->toArray(),
        ];
    }

    private function fetchRecommendedProducts(array $concerns)
    {
        $query = Product::where('is_active', true);

        if (in_array('oily', $concerns) || in_array('acne', $concerns)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%salicylic%')
                  ->orWhere('name', 'like', '%niacinamide%')
                  ->orWhere('name', 'like', '%oil-free%')
                  ->orWhere('name', 'like', '%matte%')
                  ->orWhere('name', 'like', '%acne%')
                  ->orWhere('description', 'like', '%oil-free%')
                  ->orWhere('description', 'like', '%non-comedogenic%');
            });
        }

        if (in_array('dry', $concerns)) {
            $query->orWhere(function ($q) {
                $q->where('name', 'like', '%hyaluronic%')
                  ->orWhere('name', 'like', '%moisturizer%')
                  ->orWhere('name', 'like', '%hydrating%')
                  ->orWhere('name', 'like', '%cream%')
                  ->orWhere('description', 'like', '%hydrating%')
                  ->orWhere('description', 'like', '%moisture%');
            });
        }

        if (in_array('aging', $concerns)) {
            $query->orWhere(function ($q) {
                $q->where('name', 'like', '%retinol%')
                  ->orWhere('name', 'like', '%vitamin c%')
                  ->orWhere('name', 'like', '%peptide%')
                  ->orWhere('name', 'like', '%anti-aging%')
                  ->orWhere('name', 'like', '%sunscreen%')
                  ->orWhere('name', 'like', '%spf%');
            });
        }

        if (in_array('sensitive', $concerns)) {
            $query->orWhere(function ($q) {
                $q->where('name', 'like', '%gentle%')
                  ->orWhere('name', 'like', '%sensitive%')
                  ->orWhere('name', 'like', '%soothing%')
                  ->orWhere('name', 'like', '%calming%')
                  ->orWhere('description', 'like', '%fragrance-free%')
                  ->orWhere('description', 'like', '%sensitive%');
            });
        }

        if (in_array('dark_spots', $concerns) || in_array('dullness', $concerns)) {
            $query->orWhere(function ($q) {
                $q->where('name', 'like', '%vitamin c%')
                  ->orWhere('name', 'like', '%brightening%')
                  ->orWhere('name', 'like', '%niacinamide%')
                  ->orWhere('name', 'like', '%kojic%')
                  ->orWhere('name', 'like', '%sunscreen%');
            });
        }

        if (in_array('blackheads', $concerns)) {
            $query->orWhere(function ($q) {
                $q->where('name', 'like', '%salicylic%')
                  ->orWhere('name', 'like', '%clay%')
                  ->orWhere('name', 'like', '%exfoliat%')
                  ->orWhere('name', 'like', '%bha%');
            });
        }

        // If no specific matches, get some popular products
        if ($query->count() === 0) {
            return Product::where('is_active', true)->inRandomOrder()->take(4)->get();
        }

        return $query->inRandomOrder()->take(4)->get();
    }

    private function generateResponse(array $concerns, ?string $ageInfo, string $message, array $conversationData): array
    {
        $productUsage = $this->extractProductUsage($message);
        
        // Build personalized routine
        $routine = $this->buildPersonalizedRoutine($concerns, $ageInfo);
        
        $response = "🌟 **Personalized Skincare Recommendations** 🌟\n\n";
        
        // Skin concern analysis
        $response .= "**Based on your concerns:** ";
        $concernNames = [
            'oily' => 'Oily skin',
            'acne' => 'Acne',
            'dry' => 'Dry skin',
            'sensitive' => 'Sensitive skin',
            'aging' => 'Aging concerns',
            'dark_spots' => 'Dark spots',
            'dullness' => 'Dull skin',
            'blackheads' => 'Blackheads',
            'under_eye' => 'Under-eye concerns',
            'redness' => 'Redness',
        ];
        
        $concernList = array_map(fn($c) => $concernNames[$c] ?? $c, $concerns);
        $response .= implode(', ', $concernList) . "\n\n";
        
        if ($ageInfo) {
            $ageNames = [
                'teens' => 'teens',
                'twenties' => '20s',
                'thirties' => '30s',
                'forties' => '40s',
                'fifties' => '50s',
                'sixty_plus' => '60+',
            ];
            $response .= "📅 **Age Range:** {$ageNames[$ageInfo]}\n\n";
        }

        // Product recommendations from database
        $products = $this->fetchRecommendedProducts($concerns);
        if ($products->isNotEmpty()) {
            $response .= "🛍️ **Recommended Products from Our Shop:**\n";
            foreach ($products as $product) {
                $response .= "• **{$product->name}** - \${$product->price}\n";
            }
            $response .= "\n";
        }

        // Routine recommendation
        $response .= $routine;
        
        // Tips based on concerns
        $response .= "\n💡 **Quick Tips:**\n";
        
        if (in_array('oily', $concerns) || in_array('acne', $concerns)) {
            $response .= "• Avoid touching your face throughout the day\n";
            $response .= "• Change pillowcases twice a week\n";
            $response .= "• Use non-comedogenic (won't clog pores) products\n";
            $response .= "• Consider adding a salicylic acid or benzoyl peroxide treatment\n";
        }
        if (in_array('dry', $concerns)) {
            $response .= "• Drink plenty of water throughout the day\n";
            $response .= "• Use a humidifier in dry environments\n";
            $response .= "• Apply moisturizer immediately after cleansing\n";
            $response .= "• Avoid hot water when washing your face\n";
        }
        if (in_array('sensitive', $concerns)) {
            $response .= "• Introduce new products one at a time\n";
            $response .= "• Avoid physical scrubs — use gentle chemical exfoliants instead\n";
            $response .= "• Look for products with calming ingredients like aloe vera and centella asiatica\n";
        }
        
        $response .= "\n**Attention:** For persistent or severe skin concerns, we recommend consulting a dermatologist. 👩‍⚕️";
        
        $response .= $this->disclaimer;

        $suggestions = [
            "Show me products I can buy",
            "Tell me about a skincare routine",
            "What ingredients should I look for?",
        ];

        // Only offer ask seller if concerns were identified (not just greeting/empty)
        $askSeller = count($concerns) > 0;

        return [
            'response' => $response,
            'suggestions' => $suggestions,
            'ask_seller' => $askSeller,
            'products' => $products->pluck('id')->toArray(),
        ];
    }

    private function buildPersonalizedRoutine(array $concerns, ?string $ageInfo): string
    {
        $routine = "📋 **Recommended Daily Routine:**\n\n";
        
        // Morning routine
        $routine .= "**☀️ MORNING:**\n";
        $routine .= "1. **Gentle Cleanser** - Start your day fresh\n";
        
        if (in_array('dark_spots', $concerns) || in_array('dullness', $concerns)) {
            $routine .= "2. **Vitamin C Serum** - Brightens and protects against environmental damage\n";
        } elseif (in_array('acne', $concerns)) {
            $routine .= "2. **Niacinamide Serum** - Controls oil and reduces blemishes\n";
        } else {
            $routine .= "2. **Hydrating Toner** - Preps skin for better absorption\n";
        }
        
        $routine .= "3. **Moisturizer** - " . (in_array('oily', $concerns) ? 'Oil-free gel moisturizer' : 'Hydrating cream') . "\n";
        $routine .= "4. **Sunscreen SPF 50** 🛡️ - Essential every single day!\n\n";
        
        // Evening routine
        $routine .= "**🌙 EVENING:**\n";
        $routine .= "1. **Oil-based Cleanser** (if wearing makeup/sunscreen)\n";
        $routine .= "2. **Gentle Cleanser** - Double cleanse for deep cleaning\n";
        
        if (in_array('aging', $concerns) || ($ageInfo && in_array($ageInfo, ['thirties', 'forties', 'fifties', 'sixty_plus']))) {
            $routine .= "3. **Retinol Serum** 🌟 - Gold standard for anti-aging (start 2-3x/week)\n";
        } elseif (in_array('acne', $concerns) || in_array('blackheads', $concerns)) {
            $routine .= "3. **Salicylic Acid Treatment** - Exfoliates and unclogs pores\n";
        } else {
            $routine .= "3. **Treatment Serum** - Target your specific concerns\n";
        }
        
        $routine .= "4. **Moisturizer** - " . (in_array('dry', $concerns) ? 'Richer night cream' : 'Your regular moisturizer') . "\n";
        
        // Weekly extras
        $routine .= "\n**🗓️ WEEKLY (1-2 times/week):**\n";
        
        if (in_array('dry', $concerns) || in_array('sensitive', $concerns)) {
            $routine .= "• Hydrating sheet mask for extra moisture boost\n";
        } else {
            $routine .= "• Clay mask for deep pore cleansing\n";
            $routine .= "• Gentle physical or chemical exfoliation\n";
        }
        
        return $routine;
    }

    private function buildRoutineResponse(array $conversationData): array
    {
        $concerns = [];
        if (!empty($conversationData)) {
            foreach ($conversationData as $entry) {
                if (is_array($entry) && isset($entry['message'])) {
                    $entryConcerns = $this->analyzeConcerns(strtolower($entry['message']));
                    $concerns = array_merge($concerns, $entryConcerns);
                }
            }
        }

        $ageInfo = null;
        if (!empty($conversationData)) {
            foreach ($conversationData as $entry) {
                if (is_array($entry) && isset($entry['message'])) {
                    $info = $this->extractAgeInfo(strtolower($entry['message']), $conversationData);
                    if ($info) $ageInfo = $info;
                }
            }
        }

        $routine = $this->buildPersonalizedRoutine($concerns, $ageInfo);

        $response = "📋 **Your Personalized Skincare Routine** 📋\n\n";
        $response .= $routine;
        $response .= "\n\n💡 Would you like to see products for this routine? Feel free to ask!";
        $response .= $this->disclaimer;

        return [
            'response' => $response,
            'suggestions' => [
                "Show me products for my routine",
                "I'd like to speak with a consultant",
                "What ingredients should I look for?",
            ],
            'ask_seller' => true,
        ];
    }

    public function getGreeting(): array
    {
        return [
            'response' => $this->greeting,
            'suggestions' => [
                "I have oily skin and acne",
                "What's good for dry skin?",
                "How do I reduce dark spots?",
                "Best moisturizer for sensitive skin",
            ],
            'ask_seller' => false,
        ];
    }
}