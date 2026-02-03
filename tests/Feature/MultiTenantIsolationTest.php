<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user1;
    protected User $user2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user1 = User::factory()->create(['email' => 'user1@test.com']);
        $this->user2 = User::factory()->create(['email' => 'user2@test.com']);
    }

    public function test_user_can_only_see_their_own_clients(): void
    {
        // Create clients for user1
        $this->actingAs($this->user1);
        $client1 = Client::create([
            'name' => 'User1 Client',
            'email' => 'client1@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        // Create clients for user2
        $this->actingAs($this->user2);
        $client2 = Client::create([
            'name' => 'User2 Client',
            'email' => 'client2@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        // User1 should only see their own client
        $this->actingAs($this->user1);
        $clients = Client::all();
        $this->assertCount(1, $clients);
        $this->assertEquals('User1 Client', $clients->first()->name);

        // User2 should only see their own client
        $this->actingAs($this->user2);
        $clients = Client::all();
        $this->assertCount(1, $clients);
        $this->assertEquals('User2 Client', $clients->first()->name);
    }

    public function test_user_cannot_access_other_users_client_via_route(): void
    {
        // Create client for user1
        $this->actingAs($this->user1);
        $client = Client::create([
            'name' => 'User1 Client',
            'email' => 'client1@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        // User2 should not be able to access user1's client
        $this->actingAs($this->user2);
        $response = $this->get(route('clients.show', $client->id));
        $response->assertNotFound();
    }

    public function test_user_can_only_see_their_own_invoices(): void
    {
        // Create client and invoice for user1
        $this->actingAs($this->user1);
        $client1 = Client::create([
            'name' => 'User1 Client',
            'email' => 'client1@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);
        $invoice1 = Invoice::create([
            'client_id' => $client1->id,
            'status' => Invoice::STATUS_DRAFT,
            'type' => Invoice::TYPE_INVOICE,
            'issued_at' => now(),
        ]);

        // Create client and invoice for user2
        $this->actingAs($this->user2);
        $client2 = Client::create([
            'name' => 'User2 Client',
            'email' => 'client2@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);
        $invoice2 = Invoice::create([
            'client_id' => $client2->id,
            'status' => Invoice::STATUS_DRAFT,
            'type' => Invoice::TYPE_INVOICE,
            'issued_at' => now(),
        ]);

        // User1 should only see their own invoice
        $this->actingAs($this->user1);
        $invoices = Invoice::all();
        $this->assertCount(1, $invoices);
        $this->assertEquals($invoice1->id, $invoices->first()->id);

        // User2 should only see their own invoice
        $this->actingAs($this->user2);
        $invoices = Invoice::all();
        $this->assertCount(1, $invoices);
        $this->assertEquals($invoice2->id, $invoices->first()->id);
    }

    public function test_user_can_only_see_their_own_expenses(): void
    {
        // Create expense for user1
        $this->actingAs($this->user1);
        $expense1 = Expense::create([
            'date' => now(),
            'provider_name' => 'User1 Provider',
            'category' => Expense::CATEGORY_SOFTWARE,
            'amount_ht' => 100,
            'vat_rate' => 17,
            'amount_vat' => 17,
            'amount_ttc' => 117,
        ]);

        // Create expense for user2
        $this->actingAs($this->user2);
        $expense2 = Expense::create([
            'date' => now(),
            'provider_name' => 'User2 Provider',
            'category' => Expense::CATEGORY_SOFTWARE,
            'amount_ht' => 200,
            'vat_rate' => 17,
            'amount_vat' => 34,
            'amount_ttc' => 234,
        ]);

        // User1 should only see their own expense
        $this->actingAs($this->user1);
        $expenses = Expense::all();
        $this->assertCount(1, $expenses);
        $this->assertEquals('User1 Provider', $expenses->first()->provider_name);

        // User2 should only see their own expense
        $this->actingAs($this->user2);
        $expenses = Expense::all();
        $this->assertCount(1, $expenses);
        $this->assertEquals('User2 Provider', $expenses->first()->provider_name);
    }

    public function test_user_id_is_automatically_assigned_on_creation(): void
    {
        $this->actingAs($this->user1);

        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        $this->assertEquals($this->user1->id, $client->user_id);
    }

    public function test_belongs_to_user_method_works(): void
    {
        $this->actingAs($this->user1);

        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        $this->assertTrue($client->belongsToUser($this->user1));
        $this->assertFalse($client->belongsToUser($this->user2));
        $this->assertTrue($client->belongsToAuthUser());
    }

    public function test_for_user_scope_bypasses_global_scope(): void
    {
        // Create clients for both users
        $this->actingAs($this->user1);
        $client1 = Client::create([
            'name' => 'User1 Client',
            'email' => 'client1@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        $this->actingAs($this->user2);
        $client2 = Client::create([
            'name' => 'User2 Client',
            'email' => 'client2@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        // While logged as user2, use forUser scope to get user1's clients
        $user1Clients = Client::forUser($this->user1)->get();
        $this->assertCount(1, $user1Clients);
        $this->assertEquals('User1 Client', $user1Clients->first()->name);
    }

    public function test_without_user_scope_returns_all_records(): void
    {
        // Create clients for both users
        $this->actingAs($this->user1);
        Client::create([
            'name' => 'User1 Client',
            'email' => 'client1@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        $this->actingAs($this->user2);
        Client::create([
            'name' => 'User2 Client',
            'email' => 'client2@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        // Using withoutUserScope should return all clients
        $allClients = Client::withoutUserScope()->get();
        $this->assertCount(2, $allClients);
    }

    public function test_user_relationship_works(): void
    {
        $this->actingAs($this->user1);

        $client = Client::create([
            'name' => 'Test Client',
            'email' => 'test@test.com',
            'type' => 'b2b',
            'country_code' => 'LU',
        ]);

        $this->assertInstanceOf(User::class, $client->user);
        $this->assertEquals($this->user1->id, $client->user->id);
    }
}
