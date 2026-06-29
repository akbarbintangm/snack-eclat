<?php

namespace Tests\Feature;

use App\Features\Snacks\Models\Snack;
use App\Features\Transactions\Models\Transaction;
use App\Features\Eclat\Models\EclatRun;
use App\Features\Eclat\Models\HasilEclat;
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

    public function test_database_seeder_prepares_only_login_users(): void
    {
        $this->seed();

        $admin = User::query()->where('username', 'admin')->firstOrFail();
        $owner = User::query()->where('username', 'owner')->firstOrFail();

        $this->assertSame('admin', $admin->level);
        $this->assertSame('owner', $owner->level);
        $this->assertTrue(Hash::check('password', $admin->password));
        $this->assertTrue(Hash::check('password', $owner->password));
        $this->assertSame(2, User::query()->count());
        $this->assertSame(0, Snack::query()->count());
        $this->assertSame(0, Transaction::query()->count());
        $this->assertSame(0, EclatRun::query()->count());
        $this->assertSame(0, HasilEclat::query()->count());
    }
}
