<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Hilinkz\DEAccounting\Models\DeAccount;
use Hilinkz\DEAccounting\Models\DeAccountTransaction;
use Database\Seeders\TestChartOfAccountsSeeder;

class TrialBalanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test chart of accounts
        $this->seed(TestChartOfAccountsSeeder::class);
    }

    /**
     * Test trial balance equals zero for seeded period
     */
    public function test_trial_balance_equals_zero_for_seeded_period(): void
    {
        $user = User::factory()->create();

        // Create some test transactions
        $this->createTestTransactions();

        // Get trial balance
        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'account_id',
                        'account_title',
                        'debit_balance',
                        'credit_balance',
                    ]
                ],
                'totals' => [
                    'total_debits',
                    'total_credits',
                    'net_balance',
                ]
            ]);

        $data = $response->json();

        // Trial balance should net to zero
        $this->assertEquals(0, $data['totals']['net_balance']);
        $this->assertEquals($data['totals']['total_debits'], $data['totals']['total_credits']);
    }

    /**
     * Test trial balance filters by date range
     */
    public function test_trial_balance_filters_by_date_range(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        // Create transactions in different periods
        $oldDate = now()->subMonths(3)->format('Y-m-d');
        $recentDate = now()->format('Y-m-d');

        // Old transaction
        DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($cashAccount)
            ->onDate($oldDate)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(10000)
            ->forAccount($revenueAccount)
            ->onDate($oldDate)
            ->create();

        // Recent transaction
        DeAccountTransaction::factory()
            ->debit()
            ->amount(5000)
            ->forAccount($cashAccount)
            ->onDate($recentDate)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(5000)
            ->forAccount($revenueAccount)
            ->onDate($recentDate)
            ->create();

        // Get trial balance for recent period only
        $startDate = now()->subMonth()->format('Y-m-d');
        $endDate = now()->format('Y-m-d');

        $response = $this->actingAs($user)
            ->getJson("/api/reports/trial-balance?start_date={$startDate}&end_date={$endDate}");

        $response->assertStatus(200);

        $data = $response->json();

        // Should only include recent transactions
        $this->assertEquals(10000, $data['totals']['total_debits']); // Only recent transaction
        $this->assertEquals(10000, $data['totals']['total_credits']);
        $this->assertEquals(0, $data['totals']['net_balance']);
    }

    /**
     * Test trial balance filters by entity (multi-tenant)
     */
    public function test_trial_balance_filters_by_entity(): void
    {
        $user = User::factory()->create();

        // Create accounts for different entities
        $entity1Account = DeAccount::factory()->create(['company_id' => 1]);
        $entity2Account = DeAccount::factory()->create(['company_id' => 2]);

        // Create transactions for entity 1
        DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($entity1Account)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(10000)
            ->forAccount($entity1Account)
            ->create();

        // Create transactions for entity 2
        DeAccountTransaction::factory()
            ->debit()
            ->amount(5000)
            ->forAccount($entity2Account)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(5000)
            ->forAccount($entity2Account)
            ->create();

        // Get trial balance for entity 1 only
        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance?entity_id=1');

        $response->assertStatus(200);

        $data = $response->json();

        // Should only include entity 1 transactions
        $this->assertEquals(10000, $data['totals']['total_debits']);
        $this->assertEquals(10000, $data['totals']['total_credits']);
        $this->assertEquals(0, $data['totals']['net_balance']);
    }

    /**
     * Test trial balance filters by currency
     */
    public function test_trial_balance_filters_by_currency(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        // Create transactions in different currencies (if supported)
        DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($cashAccount)
            ->state(['currency' => 'USD'])
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(10000)
            ->forAccount($revenueAccount)
            ->state(['currency' => 'USD'])
            ->create();

        DeAccountTransaction::factory()
            ->debit()
            ->amount(5000)
            ->forAccount($cashAccount)
            ->state(['currency' => 'EUR'])
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(5000)
            ->forAccount($revenueAccount)
            ->state(['currency' => 'EUR'])
            ->create();

        // Get trial balance for USD only
        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance?currency=USD');

        $response->assertStatus(200);

        $data = $response->json();

        // Should only include USD transactions
        $this->assertEquals(10000, $data['totals']['total_debits']);
        $this->assertEquals(10000, $data['totals']['total_credits']);
        $this->assertEquals(0, $data['totals']['net_balance']);
    }

    /**
     * Test trial balance with no transactions
     */
    public function test_trial_balance_with_no_transactions(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance');

        $response->assertStatus(200);

        $data = $response->json();

        // Should have zero totals
        $this->assertEquals(0, $data['totals']['total_debits']);
        $this->assertEquals(0, $data['totals']['total_credits']);
        $this->assertEquals(0, $data['totals']['net_balance']);
        $this->assertEmpty($data['data']);
    }

    /**
     * Test trial balance with unbalanced transactions
     */
    public function test_trial_balance_with_unbalanced_transactions(): void
    {
        $user = User::factory()->create();

        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        // Create unbalanced transactions (should not happen in real system)
        DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($cashAccount)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(9999) // Intentionally unbalanced
            ->forAccount($revenueAccount)
            ->create();

        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance');

        $response->assertStatus(200);

        $data = $response->json();

        // Should show the imbalance
        $this->assertEquals(10000, $data['totals']['total_debits']);
        $this->assertEquals(9999, $data['totals']['total_credits']);
        $this->assertEquals(1, $data['totals']['net_balance']); // 1 cent difference
    }

    /**
     * Test trial balance category totals
     */
    public function test_trial_balance_category_totals(): void
    {
        $user = User::factory()->create();

        // Create transactions across different account categories
        $this->createTransactionsForAllCategories();

        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance');

        $response->assertStatus(200);

        $data = $response->json();

        // Should have category breakdown
        $this->assertArrayHasKey('categories', $data);

        $categories = $data['categories'];

        // Each category should be balanced
        foreach ($categories as $category) {
            $this->assertEquals(0, $category['net_balance']);
        }
    }

    /**
     * Test trial balance performance with large dataset
     */
    public function test_trial_balance_performance_with_large_dataset(): void
    {
        $user = User::factory()->create();

        // Create large number of transactions
        $this->createLargeTransactionDataset();

        $startTime = microtime(true);

        $response = $this->actingAs($user)
            ->getJson('/api/reports/trial-balance');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $response->assertStatus(200);

        // Should complete within reasonable time (adjust threshold as needed)
        $this->assertLessThan(2.0, $executionTime, 'Trial balance calculation took too long');

        $data = $response->json();
        $this->assertEquals(0, $data['totals']['net_balance']);
    }

    /**
     * Helper method to create test transactions
     */
    private function createTestTransactions(): void
    {
        $cashAccount = DeAccount::where('account_no', '1110')->first();
        $revenueAccount = DeAccount::where('account_no', '4100')->first();

        DeAccountTransaction::factory()
            ->debit()
            ->amount(10000)
            ->forAccount($cashAccount)
            ->create();

        DeAccountTransaction::factory()
            ->credit()
            ->amount(10000)
            ->forAccount($revenueAccount)
            ->create();
    }

    /**
     * Helper method to create transactions for all categories
     */
    private function createTransactionsForAllCategories(): void
    {
        $accounts = DeAccount::all();

        foreach ($accounts as $account) {
            if ($account->root_type == 1 || $account->root_type == 2) { // Assets or Expenses
                DeAccountTransaction::factory()
                    ->debit()
                    ->amount(1000)
                    ->forAccount($account)
                    ->create();
            } else { // Liabilities, Income, or Capital
                DeAccountTransaction::factory()
                    ->credit()
                    ->amount(1000)
                    ->forAccount($account)
                    ->create();
            }
        }
    }

    /**
     * Helper method to create large transaction dataset
     */
    private function createLargeTransactionDataset(): void
    {
        $accounts = DeAccount::all();

        // Create 1000 transactions
        for ($i = 0; $i < 1000; $i++) {
            $account = $accounts->random();
            $amount = rand(100, 10000);

            if ($account->root_type == 1 || $account->root_type == 2) {
                DeAccountTransaction::factory()
                    ->debit()
                    ->amount($amount)
                    ->forAccount($account)
                    ->create();
            } else {
                DeAccountTransaction::factory()
                    ->credit()
                    ->amount($amount)
                    ->forAccount($account)
                    ->create();
            }
        }
    }
}
