<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('concern')->nullable();
            $table->text('ai_response')->nullable();
            $table->enum('status', ['active', 'ai_only', 'seller_requested', 'seller_replied', 'completed'])->default('active');
            $table->text('conversation_data')->nullable(); // JSON store of full AI conversation
            $table->timestamps();
        });

        Schema::create('consultation_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->onDelete('cascade');
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('sender_type')->default('user'); // 'user', 'ai', 'admin'
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('consultation_messages');
        Schema::dropIfExists('consultations');
    }
};