<?php

// database/migrations/xxxx_xx_xx_xxxxxx_add_deleted_at_to_parts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->softDeletes()->after('updated_at'); // adds deleted_at
        });
    }
    public function down(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
