<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });

        // Update existing 'completed' statuses to 'delivered'
        \Illuminate\Support\Facades\DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'delivered']);
    }

    public function down(): void {
        // Revert 'delivered' statuses back to 'completed'
        \Illuminate\Support\Facades\DB::table('orders')
            ->where('status', 'delivered')
            ->update(['status' => 'completed']);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->change();
        });
    }
};
