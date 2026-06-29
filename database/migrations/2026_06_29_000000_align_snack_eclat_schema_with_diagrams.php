<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 100)->nullable()->unique()->after('id');
            }

            if (! Schema::hasColumn('users', 'level')) {
                $table->string('level', 100)->default('admin')->after('password');
            }

            if (! Schema::hasColumn('users', 'api_token_hash')) {
                $table->string('api_token_hash', 64)->nullable()->unique()->after('level');
            }
        });

        DB::table('users')
            ->whereNull('username')
            ->orderBy('id')
            ->get(['id', 'email'])
            ->each(function (object $user): void {
                DB::table('users')->where('id', $user->id)->update([
                    'username' => $this->usernameFromEmail($user->email, $user->id),
                    'level' => DB::raw("COALESCE(level, 'admin')"),
                ]);
            });

        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('username', 100)->nullable(false)->change();
            });
        }

        Schema::table('snacks', function (Blueprint $table): void {
            if (! Schema::hasColumn('snacks', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });

        if (DB::getDriverName() !== 'sqlite') {
            $this->dropIndexIfExists('snacks', 'snacks_name_unique');
            $this->dropIndexIfExists('snacks', 'snacks_status_name_index');

            Schema::table('snacks', function (Blueprint $table): void {
                $table->string('name', 1024)->change();
            });
        }

        Schema::table('transactions', function (Blueprint $table): void {
            if (! Schema::hasColumn('transactions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('hasil_eclat', function (Blueprint $table): void {
            if (! Schema::hasColumn('hasil_eclat', 'transaction_detail_id')) {
                $table->foreignId('transaction_detail_id')
                    ->nullable()
                    ->after('eclat_run_id')
                    ->constrained('transaction_details')
                    ->nullOnDelete();
            }
        });

        Schema::table('eclat_runs', function (Blueprint $table): void {
            if (! Schema::hasColumn('eclat_runs', 'filter_type')) {
                $table->string('filter_type', 20)->default('all')->after('min_confidence');
            }

            if (! Schema::hasColumn('eclat_runs', 'date_from')) {
                $table->date('date_from')->nullable()->after('filter_type');
            }

            if (! Schema::hasColumn('eclat_runs', 'date_to')) {
                $table->date('date_to')->nullable()->after('date_from');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hasil_eclat', function (Blueprint $table): void {
            if (Schema::hasColumn('hasil_eclat', 'transaction_detail_id')) {
                $table->dropConstrainedForeignId('transaction_detail_id');
            }
        });

        Schema::table('transactions', function (Blueprint $table): void {
            if (Schema::hasColumn('transactions', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('snacks', function (Blueprint $table): void {
            if (Schema::hasColumn('snacks', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'api_token_hash')) {
                $table->dropUnique(['api_token_hash']);
                $table->dropColumn('api_token_hash');
            }

            if (Schema::hasColumn('users', 'level')) {
                $table->dropColumn('level');
            }

            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });

        Schema::table('eclat_runs', function (Blueprint $table): void {
            if (Schema::hasColumn('eclat_runs', 'date_to')) {
                $table->dropColumn('date_to');
            }

            if (Schema::hasColumn('eclat_runs', 'date_from')) {
                $table->dropColumn('date_from');
            }

            if (Schema::hasColumn('eclat_runs', 'filter_type')) {
                $table->dropColumn('filter_type');
            }
        });
    }

    private function usernameFromEmail(?string $email, int $id): string
    {
        $base = str((string) $email)->before('@')->replaceMatches('/[^A-Za-z0-9_]+/', '_')->lower()->trim('_')->toString();

        return $base !== '' ? "{$base}_{$id}" : "user_{$id}";
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        $exists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();

        if ($exists) {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$index}`");
        }
    }
};
