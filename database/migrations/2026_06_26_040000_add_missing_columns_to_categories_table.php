<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            // Some environments may already have these columns.
            if (!\Illuminate\Support\Facades\Schema::hasColumn('categories', 'slug')) {
                $table->string('slug')->unique()->after('name');
            }

            if (!\Illuminate\Support\Facades\Schema::hasColumn('categories', 'image')) {
                $table->string('image')->nullable()->after('description');
            }
        });
    }

    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['slug', 'image']);
        });
    }
};