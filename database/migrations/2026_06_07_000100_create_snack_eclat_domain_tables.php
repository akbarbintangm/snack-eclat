<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snacks', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('status', 30)->default('active');
            $this->auditColumns($table);
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->unique('name');
            $table->index(['status', 'name']);
            $this->auditForeignKeys($table);
        });

        Schema::create('transactions', function (Blueprint $table): void {
            $table->id();
            $table->string('reference_no', 50)->nullable();
            $table->date('transaction_date');
            $table->string('status', 30)->default('active');
            $this->auditColumns($table);
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->unique('reference_no');
            $table->index(['status', 'transaction_date']);
            $this->auditForeignKeys($table);
        });

        Schema::create('transaction_details', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
            $table->foreignId('snack_id')->constrained('snacks')->restrictOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->string('status', 30)->default('active');
            $this->auditColumns($table);
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->unique(['transaction_id', 'snack_id']);
            $table->index(['snack_id', 'status']);
            $this->auditForeignKeys($table);
        });

        Schema::create('eclat_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('run_code', 50)->unique();
            $table->decimal('min_support', 8, 4)->default(30);
            $table->decimal('min_confidence', 8, 4)->default(50);
            $table->unsignedInteger('total_transactions')->default(0);
            $table->unsignedInteger('total_items')->default(0);
            $table->unsignedInteger('frequent_itemset_count')->default(0);
            $table->unsignedInteger('rule_count')->default(0);
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->json('tid_list')->nullable();
            $table->json('frequent_itemsets')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('completed');
            $this->auditColumns($table);
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['min_support', 'min_confidence']);
            $this->auditForeignKeys($table);
        });

        Schema::create('hasil_eclat', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('eclat_run_id')->nullable()->constrained('eclat_runs')->nullOnDelete();
            $table->string('combination_item', 200);
            $table->json('antecedent_items');
            $table->json('consequent_items');
            $table->unsignedInteger('transaction_count')->default(0);
            $table->decimal('support', 8, 4);
            $table->decimal('confidence', 8, 4);
            $table->decimal('lift_ratio', 8, 4)->nullable();
            $table->json('tid_list')->nullable();
            $table->text('recommendation')->nullable();
            $table->string('status', 30)->default('active');
            $this->auditColumns($table);
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->index(['eclat_run_id', 'support', 'confidence']);
            $table->index(['status', 'confidence']);
            $this->auditForeignKeys($table);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_eclat');
        Schema::dropIfExists('eclat_runs');
        Schema::dropIfExists('transaction_details');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('snacks');
    }

    private function auditColumns(Blueprint $table): void
    {
        $table->unsignedBigInteger('created_by')->nullable();
        $table->unsignedBigInteger('updated_by')->nullable();
    }

    private function auditForeignKeys(Blueprint $table): void
    {
        $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
        $table->foreign('deleted_by')->references('id')->on('users')->nullOnDelete();
    }
};
