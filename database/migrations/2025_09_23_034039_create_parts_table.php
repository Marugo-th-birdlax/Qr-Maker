<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('no')->nullable()->index();  // ลำดับจากไฟล์ (ถ้ามี)
            $table->string('part_no')->index();                     // Part No
            $table->string('part_name')->nullable();
            $table->string('supplier_name')->nullable();
            $table->string('supplier_code', 64)->nullable()->index();
            $table->unsignedInteger('moq')->nullable();
            $table->date('date')->nullable();                       // คอลัมน์ Date
            // ถ้าจะเก็บ payload สำหรับ QR
            $table->text('qr_payload')->nullable();
            $table->timestamps();

            $table->unique(['part_no']); // กันซ้ำด้วย Part No (ปรับตามจริง)
        });
    }

};
