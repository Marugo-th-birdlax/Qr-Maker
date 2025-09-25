<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 32)->unique();     // รหัสพนักงาน
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('nickname', 100)->nullable();
            $table->string('department', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('email')->unique();
            $table->string('password');                       // เก็บ hash
            $table->enum('role', ['admin','manager','user'])->default('user');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('users');
    }
};
