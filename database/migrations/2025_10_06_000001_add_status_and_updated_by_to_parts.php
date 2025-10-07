<?php

// database/migrations/2025_10_06_000001_add_status_and_updated_by_to_parts.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::table('parts', function (Blueprint $table) {
      $table->boolean('is_active')->default(true)->after('qr_payload');
      $table->timestamp('deactivated_at')->nullable()->after('is_active');
      $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
      // ถ้าตาราง users ชัดเจน จะคอนสเตรนต์ได้ (ไม่ชัวร์ให้คอมเมนต์ไว้ก่อน)
      // $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
    });
  }
  public function down(): void {
    Schema::table('parts', function (Blueprint $table) {
      // ถ้าเคยเพิ่ม FK ต้องดรอป FK ก่อน
      // $table->dropForeign(['updated_by']);
      $table->dropColumn(['is_active','deactivated_at','updated_by']);
    });
  }
};
