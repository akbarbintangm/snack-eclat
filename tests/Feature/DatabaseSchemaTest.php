<?php

namespace Tests\Feature;

use App\Features\Eclat\Models\EclatRun;
use App\Features\Transactions\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_schema_matches_snack_eclat_flow_requirements(): void
    {
        $this->assertTrue(Schema::hasColumns('users', ['username', 'password', 'level', 'api_token_hash']));
        $this->assertTrue(Schema::hasColumns('snacks', ['id', 'user_id', 'name']));
        $this->assertTrue(Schema::hasColumns('transactions', ['id', 'user_id', 'transaction_date']));
        $this->assertTrue(Schema::hasColumns('transaction_details', ['id', 'transaction_id', 'snack_id']));
        $this->assertTrue(Schema::hasColumns('hasil_eclat', ['id', 'transaction_detail_id', 'combination_item', 'support', 'confidence']));
        $this->assertTrue(Schema::hasColumns('eclat_runs', ['filter_type', 'date_from', 'date_to']));
    }

    public function test_database_seeder_prepares_login_users_transactions_and_initial_eclat_run(): void
    {
        $this->seed();

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $owner = User::query()->where('username', 'owner')->firstOrFail();

        $this->assertSame('admin', $admin->level);
        $this->assertSame('owner', $owner->level);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue(Hash::check('password', $owner->password));
        $this->assertGreaterThanOrEqual(10, Transaction::query()->count());
        $this->assertGreaterThanOrEqual(1, EclatRun::query()->count());
    }
}
