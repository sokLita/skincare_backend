<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use App\Models\ConsultationMessage;
use App\Services\AISkincareService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConsultationController extends Controller
{
    protected AISkincareService $aiService;

    public function __construct(AISkincareService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Get the user's active consultation or create a new one
     */
    public function getOrCreateConsultation(Request $request)
    {
        try {
            $user = $request->user();

            // Find active consultation (not completed)
            $consultation = Consultation::where('user_id', $user->id)
                ->whereIn('status', ['active', 'ai_only', 'seller_requested', 'seller_replied'])
                ->with('messages')
                ->latest()
                ->first();

            if (!$consultation) {
                $consultation = Consultation::create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'conversation_data' => [],
                ]);

                // Add AI greeting as first message
                $greeting = $this->aiService->getGreeting();
                $this->addMessage($consultation, null, 'ai', $greeting['response']);
            }

            return response()->json([
                'consultation' => $this->formatConsultation($consultation),
            ]);
        } catch (\Exception $e) {
            Log::error('ConsultationController@getOrCreateConsultation: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to start consultation.'], 500);
        }
    }

    /**
     * Send a message in the consultation (to AI or request seller)
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
                'request_seller' => 'boolean',
            ]);

            $user = $request->user();
            $message = trim($request->message);
            $requestSeller = $request->boolean('request_seller');

            // Get or create consultation
            $consultation = Consultation::where('user_id', $user->id)
                ->whereIn('status', ['active', 'ai_only', 'seller_requested', 'seller_replied'])
                ->latest()
                ->first();

            if (!$consultation) {
                $consultation = Consultation::create([
                    'user_id' => $user->id,
                    'status' => 'active',
                    'conversation_data' => [],
                ]);
            }

            // Save user message
            $this->addMessage($consultation, $user->id, 'user', $message);

            // Update conversation data
            $conversationData = $consultation->conversation_data ?? [];
            $conversationData[] = [
                'type' => 'user',
                'message' => $message,
                'timestamp' => now()->toIso8601String(),
            ];

            if ($requestSeller) {
                // User requested seller advice
                $consultation->update([
                    'status' => 'seller_requested',
                    'concern' => $consultation->concern ?? $message,
                    'conversation_data' => $conversationData,
                ]);

                // Add notification for admin
                $this->createSellerRequestNotification($consultation, $user);

                return response()->json([
                    'message' => 'Your request has been sent to our skincare consultant. They will get back to you shortly! 💬',
                    'type' => 'seller_requested',
                    'consultation' => $this->formatConsultation($consultation->fresh()),
                ]);
            }

            // Process with AI
            $aiResponse = $this->aiService->processMessage($message, $conversationData);

            // Save AI response message
            $this->addMessage($consultation, null, 'ai', $aiResponse['response']);

            // Update conversation data with AI response
            $conversationData[] = [
                'type' => 'ai',
                'message' => $aiResponse['response'],
                'timestamp' => now()->toIso8601String(),
            ];

            // Update consultation
            $consultation->update([
                'concern' => $consultation->concern ?? $message,
                'status' => 'ai_only',
                'conversation_data' => $conversationData,
            ]);

            return response()->json([
                'message' => $aiResponse['response'],
                'type' => 'ai',
                'suggestions' => $aiResponse['suggestions'] ?? [],
                'ask_seller' => $aiResponse['ask_seller'] ?? false,
                'consultation' => $this->formatConsultation($consultation->fresh()),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Please provide a valid message.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('ConsultationController@sendMessage: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to process your message. Please try again.'], 500);
        }
    }

    /**
     * Get user's consultation history
     */
    public function myConsultations(Request $request)
    {
        try {
            $user = $request->user();
            $consultations = Consultation::where('user_id', $user->id)
                ->with('messages')
                ->latest()
                ->paginate(10);

            $formatted = $consultations->map(function ($c) {
                return $this->formatConsultation($c);
            });

            return response()->json([
                'consultations' => $formatted,
                'pagination' => [
                    'current_page' => $consultations->currentPage(),
                    'last_page' => $consultations->lastPage(),
                    'total' => $consultations->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('ConsultationController@myConsultations: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load consultations.'], 500);
        }
    }

    /**
     * Admin: Get all consultations with seller requests
     */
    public function adminConsultations(Request $request)
    {
        try {
            $status = $request->query('status');

            $query = Consultation::with(['user', 'messages' => function ($q) {
                $q->latest()->take(5);
            }]);

            if ($status && in_array($status, ['active', 'ai_only', 'seller_requested', 'seller_replied', 'completed'])) {
                $query->where('status', $status);
            }

            // Show seller_requested first, then by latest
            $consultations = $query->orderByRaw("FIELD(status, 'seller_requested', 'seller_replied', 'active', 'ai_only', 'completed')")
                ->latest()
                ->paginate(20);

            $formatted = $consultations->map(function ($c) {
                return $this->formatConsultation($c);
            });

            return response()->json([
                'consultations' => $formatted,
                'pagination' => [
                    'current_page' => $consultations->currentPage(),
                    'last_page' => $consultations->lastPage(),
                    'total' => $consultations->total(),
                ],
                'unread_seller_requests' => Consultation::where('status', 'seller_requested')->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('ConsultationController@adminConsultations: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load consultations.'], 500);
        }
    }

    /**
     * Admin: Get single consultation details with all messages
     */
    public function adminConsultationDetail(Request $request, $id)
    {
        try {
            $consultation = Consultation::with(['user', 'messages.sender'])
                ->findOrFail($id);

            return response()->json([
                'consultation' => $this->formatConsultation($consultation),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found.'], 404);
        } catch (\Exception $e) {
            Log::error('ConsultationController@adminConsultationDetail: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to load consultation details.'], 500);
        }
    }

    /**
     * Admin: Reply to a consultation (seller reply)
     */
    public function adminReply(Request $request, $id)
    {
        try {
            $request->validate([
                'message' => 'required|string|max:2000',
            ]);

            $user = $request->user();
            $consultation = Consultation::findOrFail($id);

            // Save admin message
            $this->addMessage($consultation, $user->id, 'admin', $request->message);

            // Update status
            if ($consultation->status === 'seller_requested') {
                $consultation->update(['status' => 'seller_replied']);
            }

            // Update conversation data
            $conversationData = $consultation->conversation_data ?? [];
            $conversationData[] = [
                'type' => 'admin',
                'message' => $request->message,
                'timestamp' => now()->toIso8601String(),
            ];
            $consultation->update(['conversation_data' => $conversationData]);

            return response()->json([
                'message' => 'Reply sent successfully.',
                'consultation' => $this->formatConsultation($consultation->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found.'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'Please provide a valid message.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('ConsultationController@adminReply: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to send reply. Please try again.'], 500);
        }
    }

    /**
     * Admin: Get unread seller request count
     */
    public function unreadSellerRequests(Request $request)
    {
        try {
            $count = Consultation::where('status', 'seller_requested')->count();
            return response()->json(['count' => $count]);
        } catch (\Exception $e) {
            Log::error('ConsultationController@unreadSellerRequests: ' . $e->getMessage());
            return response()->json(['count' => 0]);
        }
    }

    /**
     * Admin: Complete a consultation
     */
    public function completeConsultation(Request $request, $id)
    {
        try {
            $consultation = Consultation::findOrFail($id);
            $consultation->update(['status' => 'completed']);
            return response()->json([
                'message' => 'Consultation marked as completed.',
                'consultation' => $this->formatConsultation($consultation->fresh()),
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Consultation not found.'], 404);
        } catch (\Exception $e) {
            Log::error('ConsultationController@completeConsultation: ' . $e->getMessage());
            return response()->json(['message' => 'Unable to update consultation.'], 500);
        }
    }

    /**
     * Helper: Add a message to a consultation
     */
    private function addMessage(Consultation $consultation, $senderId, string $senderType, string $message): ConsultationMessage
    {
        return ConsultationMessage::create([
            'consultation_id' => $consultation->id,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $message,
        ]);
    }

    /**
     * Helper: Create notification for admin when seller is requested
     */
    private function createSellerRequestNotification(Consultation $consultation, $user)
    {
        try {
            // We'll store notifications in the chat system for simplicity
            // Can be extended to a dedicated notifications table later
            \Illuminate\Support\Facades\Log::info('Seller requested for consultation #' . $consultation->id . ' by user ' . $user->name);
        } catch (\Exception $e) {
            // Silent fail for notification
        }
    }

    /**
     * Helper: Format consultation for API response
     */
    private function formatConsultation(Consultation $consultation): array
    {
        $messages = $consultation->messages()->orderBy('created_at')->get()->map(function ($msg) {
            return [
                'id' => $msg->id,
                'sender_type' => $msg->sender_type,
                'message' => $msg->message,
                'sender_name' => $msg->sender?->name,
                'is_read' => $msg->is_read,
                'created_at' => $msg->created_at,
            ];
        });

        return [
            'id' => $consultation->id,
            'user_id' => $consultation->user_id,
            'user_name' => $consultation->user?->name,
            'user_email' => $consultation->user?->email,
            'concern' => $consultation->concern,
            'status' => $consultation->status,
            'messages' => $messages,
            'created_at' => $consultation->created_at,
            'updated_at' => $consultation->updated_at,
        ];
    }
}