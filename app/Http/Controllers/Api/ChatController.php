<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\User;

class ChatController extends Controller
{
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
     * Admin: Get all unique conversations (grouped by user)
     */
    public function conversations(Request $request)
    {
        $userIds = ChatMessage::select('user_id')
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
        $messages = ChatMessage::where('user_id', $userId)
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Admin: Reply to a customer conversation
     */
    public function replyMessage(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

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