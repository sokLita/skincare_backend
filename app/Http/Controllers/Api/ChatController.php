<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\TelegramUser;
use App\Services\TelegramBotService;

class ChatController extends Controller
{
    protected TelegramBotService $telegramBot;

    public function __construct(TelegramBotService $telegramBot)
    {
        $this->telegramBot = $telegramBot;
    }

    /**
     * Customer: Get all messages for the authenticated user
     */
    public function myMessages(Request $request)
    {
        $messages = ChatMessage::where('user_id', $request->user()->id)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Customer: Send a new message (customer to admin)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = ChatMessage::create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_admin' => false,
            'is_read' => false,
        ]);

        $message->load('user:id,name');

        return response()->json($message, 201);
    }

    /**
     * Admin: Get all unique conversations (grouped by user), including Telegram
     */
    public function conversations(Request $request)
    {
        $userIds = ChatMessage::whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        $conversations = [];
        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $lastMessage = ChatMessage::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = ChatMessage::where('user_id', $userId)
                ->where('is_admin', false)
                ->where('is_read', false)
                ->count();

            $conversations[] = [
                'user_id' => $userId,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'source' => 'web',
                'last_message' => $lastMessage ? $lastMessage->message : '',
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                'unread_count' => $unreadCount,
            ];
        }

        // Also get Telegram conversations
        $telegramUserIds = ChatMessage::where('source', 'telegram')
            ->whereNotNull('telegram_user_id')
            ->select('telegram_user_id')
            ->distinct()
            ->pluck('telegram_user_id');

        foreach ($telegramUserIds as $tgUserId) {
            $tgUser = TelegramUser::find($tgUserId);
            if (!$tgUser) continue;

            // Skip if this telegram user is linked to a web user already shown
            if ($tgUser->user_id && $userIds->contains($tgUser->user_id)) {
                continue;
            }

            $lastMessage = ChatMessage::where('telegram_user_id', $tgUserId)
                ->orderBy('created_at', 'desc')
                ->first();

            $unreadCount = ChatMessage::where('telegram_user_id', $tgUserId)
                ->where('is_admin', false)
                ->where('is_read', false)
                ->count();

            $displayName = $tgUser->first_name;
            if ($tgUser->last_name) $displayName .= ' ' . $tgUser->last_name;
            if ($tgUser->username) $displayName .= ' (@' . $tgUser->username . ')';

            $conversations[] = [
                'user_id' => 'telegram_' . $tgUserId,
                'telegram_user_id' => $tgUserId,
                'user_name' => '📱 ' . $displayName,
                'user_email' => 'Telegram Bot',
                'source' => 'telegram',
                'last_message' => $lastMessage ? $lastMessage->message : '',
                'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                'unread_count' => $unreadCount,
            ];
        }

        // Sort by last message time descending
        usort($conversations, function ($a, $b) {
            if (!$a['last_message_time']) return 1;
            if (!$b['last_message_time']) return -1;
            return strtotime($b['last_message_time']) - strtotime($a['last_message_time']);
        });

        return response()->json($conversations);
    }

    /**
     * Admin: Get messages for a specific conversation with a user
     */
    public function conversationMessages(Request $request, $userId)
    {
        // Check if it's a Telegram conversation
        if (str_starts_with($userId, 'telegram_')) {
            $tgUserId = str_replace('telegram_', '', $userId);
            $messages = ChatMessage::where('telegram_user_id', $tgUserId)
                ->with('telegramUser:id,first_name,last_name,username')
                ->orderBy('created_at', 'asc')
                ->get();

            return response()->json($messages->map(function ($msg) {
                return [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'is_admin' => $msg->is_admin,
                    'is_read' => $msg->is_read,
                    'source' => 'telegram',
                    'sender_name' => $msg->is_admin
                        ? 'Admin'
                        : ($msg->telegramUser->first_name ?? 'Telegram User'),
                    'created_at' => $msg->created_at,
                ];
            }));
        }

        $messages = ChatMessage::where('user_id', $userId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Admin: Reply to a customer conversation (supports both web and Telegram)
     */
    public function replyMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Check if replying to a Telegram conversation
        if (str_starts_with($userId, 'telegram_')) {
            $tgUserId = str_replace('telegram_', '', $userId);
            $tgUser = TelegramUser::find($tgUserId);

            if (!$tgUser) {
                return response()->json(['error' => 'Telegram user not found'], 404);
            }

            $message = ChatMessage::create([
                'telegram_user_id' => $tgUserId,
                'admin_id' => $request->user()->id,
                'message' => $request->message,
                'is_admin' => true,
                'is_read' => false,
                'source' => 'telegram',
            ]);

            // Send the reply back to Telegram
            $this->telegramBot->sendAdminReply($tgUser, $request->message);

            return response()->json($message, 201);
        }

        $message = ChatMessage::create([
            'user_id' => $userId,
            'admin_id' => $request->user()->id,
            'message' => $request->message,
            'is_admin' => true,
            'is_read' => false,
        ]);

        $message->load('user:id,name');

        return response()->json($message, 201);
    }

    /**
     * Admin: Mark messages as read for a conversation
     */
    public function markAsRead(Request $request, $userId)
    {
        ChatMessage::where('user_id', $userId)
            ->where('is_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }

    /**
     * Customer: Get unread message count
     */
    public function unreadCount(Request $request)
    {
        $count = ChatMessage::where('user_id', $request->user()->id)
            ->where('is_admin', true)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Customer: Mark admin messages as read
     */
    public function markCustomerRead(Request $request)
    {
        ChatMessage::where('user_id', $request->user()->id)
            ->where('is_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }
}