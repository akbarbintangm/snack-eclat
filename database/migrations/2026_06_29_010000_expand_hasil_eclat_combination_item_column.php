<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('hasil_eclat') || ! Schema::hasColumn('hasil_eclat', 'combination_item')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('hasil_eclat', function (Blueprint $table): void {
            $table->text('combination_item')->change();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('hasil_eclat') || ! Schema::hasColumn('hasil_eclat', 'combination_item')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('hasil_eclat', function (Blueprint $table): void {
            $table->string('combination_item', 255)->change();
        });
    }
};
