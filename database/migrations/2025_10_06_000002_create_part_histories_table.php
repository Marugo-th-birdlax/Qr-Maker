<?php
// database/migrations/2025_10_06_000002_create_part_histories_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('part_histories', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('part_id')->index();
      $table->unsignedBigInteger('user_id')->nullable()->index(); // คนกระทำ
      $table->string('action', 32); // create, update, delete, activate, deactivate
      $table->json('before')->nullable();
      $table->json('after')->nullable();
      $table->json('changed_fields')->nullable(); // รายชื่อคีย์ที่เปลี่ยน (array)
      $table->string('note', 255)->nullable(); // เผื่ออธิบายเพิ่ม
      $table->timestamps();

      // คอนสเตรนต์ ถ้า schema ชัดเจน
      // $table->foreign('part_id')->references('id')->on('parts')->cascadeOnDelete();
      // $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
    });
  }
  public function down(): void {
    Schema::dropIfExists('part_histories');
  }
};
