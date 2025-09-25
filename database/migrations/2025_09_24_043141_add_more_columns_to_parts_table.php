<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_more_columns_to_parts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->string('pic', 50)->nullable()->after('no');
            $table->string('type', 50)->nullable()->after('pic');            // ชื่อคอลัมน์ 'type' ใช้ได้
            $table->string('supplier', 100)->nullable()->after('supplier_code'); // กลุ่ม/หมวด supplier
            $table->string('location', 100)->nullable()->after('supplier');
            $table->unsignedInteger('qty_per_box')->nullable()->after('moq');
            $table->text('remark')->nullable()->after('qty_per_box');
            $table->string('item_no', 100)->nullable()->after('remark');
            $table->string('unit', 20)->nullable()->after('item_no');

            // (ทางเลือก) ดัชนีสำหรับกรองบ่อย ๆ
            $table->index(['type']);
            $table->index(['supplier']);
            $table->index(['location']);
        });
    }

    public function down(): void {
        Schema::table('parts', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['supplier']);
            $table->dropIndex(['location']);
            $table->dropColumn([
                'pic','type','supplier','location',
                'qty_per_box','remark','item_no','unit'
            ]);
        });
    }
};
