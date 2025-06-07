<?php

namespace Tests\Unit;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_can_be_retrieved_by_id()
    {
        $transaction = Transaction::factory()->create();
        $retrievedTransaction = Transaction::find($transaction->id);

        $this->assertEquals($transaction->id, $retrievedTransaction->id);
    }

    public function test_multiple_transactions_can_be_created()
    {
        Transaction::factory()->count(5)->create();
        $this->assertDatabaseCount('transactions', 5);
    }

    public function test_can_rollback_transaction_creation()
    {
        \DB::beginTransaction();
        Transaction::create([
            'description' => 'Teste',
            'amount' => 1,
            'date' => now(),
            'category' => 'Teste'
        ]);
        \DB::rollBack();

        $this->assertDatabaseMissing('transactions', ['description' => 'Teste']);
    }

    public function test_database_persists_transaction_data_correctly()
    {
        Transaction::create([
            'description' => 'Banco',
            'amount' => 9.99,
            'date' => '2024-06-01',
            'category' => 'Teste'
        ]);

        $this->assertDatabaseHas('transactions', ['category' => 'Teste']);
    }

    public function test_transaction_is_missing_after_deleted()
    {
        $transaction = Transaction::factory()->create();
        $transaction->delete();
        $this->assertModelMissing($transaction);
    }

    public function test_transaction_can_be_filtered_by_category()
    {
        Transaction::factory()->create(['category' => 'Alimentação']);
        Transaction::factory()->create(['category' => 'Transporte']);

        $transactions = Transaction::where('category', 'Alimentação')->get();
        $this->assertCount(1, $transactions);
        $this->assertEquals('Alimentação', $transactions->first()->category);
    }

    public function test_transaction_can_be_sorted_by_date()
    {
        Transaction::factory()->create(['date' => '2024-06-01']);
        Transaction::factory()->create(['date' => '2024-05-01']);

        $transactions = Transaction::orderBy('date')->get();
        $this->assertEquals('2024-05-01', $transactions->first()->date->toDateString());
    }
}
