<?php

namespace Tests\Unit;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_transaction()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Compra no mercado',
            'amount' => 120.50,
            'date' => '2024-05-01',
            'category' => 'Alimentação',
        ]);

        $response->assertRedirect(route('transactions'));
    }

    public function test_cannot_create_transaction_with_invalid_amount()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Teste',
            'amount' => 'invalido',
            'date' => '2024-06-01',
            'category' => 'Teste'
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_cannot_create_transaction_with_invalid_date()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Teste',
            'amount' => 99.99,
            'date' => 'data-invalida',
            'category' => 'Teste'
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_cannot_create_transaction_with_missing_fields()
    {
        $response = $this->post(route('transaction.store'), []);

        $response->assertSessionHasErrors(['description', 'amount', 'date', 'category']);
    }

    public function test_description_is_required()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => '',
            'amount' => 50.00,
            'date' => '2024-06-01',
            'category' => 'Transporte'
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_amount_is_required()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Aluguel',
            'amount' => '',
            'date' => '2024-06-01',
            'category' => 'Moradia'
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_date_is_required()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Energia',
            'amount' => 180,
            'date' => '',
            'category' => 'Contas'
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_category_is_required()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Internet',
            'amount' => 100,
            'date' => '2024-06-01',
            'category' => ''
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_description_must_not_exceed_255_characters()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => str_repeat('a', 256),
            'amount' => 10,
            'date' => '2024-06-01',
            'category' => 'Teste'
        ]);

        $response->assertSessionHasErrors('description');
    }

    public function test_category_must_not_exceed_100_characters()
    {
        $response = $this->post(route('transaction.store'), [
            'description' => 'Compra',
            'amount' => 10,
            'date' => '2024-06-01',
            'category' => str_repeat('b', 101)
        ]);

        $response->assertSessionHasErrors('category');
    }

    public function test_transactions_are_ordered_by_date_desc()
    {
        Transaction::factory()->create(['description' => 'Antiga', 'date' => '2022-01-01']);
        Transaction::factory()->create(['description' => 'Recente', 'date' => '2024-01-01']);

        $response = $this->get(route('transactions'));
        $response->assertSeeInOrder(['Recente', 'Antiga']);
    }

    public function test_index_returns_ok()
    {
        $response = $this->get(route('transactions'));
        $response->assertStatus(200);
    }

    public function test_transaction_can_be_edited()
    {
        $transaction = Transaction::factory()->create(['description' => 'Antigo Nome']);

        $response = $this->patch(route('transaction.update', $transaction->id), [
            'description' => 'Novo Nome',
            'amount' => 100,
            'date' => now()->format('Y-m-d'),
            'category' => 'Outros'
        ]);

        $response->assertRedirect(route('transactions'));
        $this->assertDatabaseHas('transactions', ['description' => 'Novo Nome']);
    }

    public function test_edit_transaction_form_is_accessible()
    {
        $transaction = Transaction::factory()->create();
        $response = $this->get(route('transaction.edit', $transaction->id));

        $response->assertStatus(200);
        $response->assertSee($transaction->description);
    }

    public function test_cannot_update_transaction_with_invalid_data()
    {
        $transaction = Transaction::factory()->create();

        $response = $this->patch(route('transaction.update', $transaction->id), [
            'description' => '',
            'amount' => 'texto',
            'date' => 'hoje',
            'category' => ''
        ]);

        $response->assertSessionHasErrors(['description', 'amount', 'date', 'category']);
    }

    public function test_edit_page_returns_404_if_transaction_does_not_exist()
    {
        $response = $this->get(route('transaction.edit', 999));
        $response->assertNotFound();
    }

    public function test_transaction_can_be_destroyed()
    {
        $transaction = Transaction::factory()->create();
        $this->delete(route('transaction.destroy', $transaction->id));
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_deleting_nonexistent_transaction_returns_404()
    {
        $response = $this->delete(route('transaction.destroy', 99999));
        $response->assertNotFound();
    }

    public function test_deleted_transaction_is_not_listed()
    {
        $transaction = Transaction::factory()->create();
        $this->delete(route('transaction.destroy', $transaction->id));
        $response = $this->get(route('transactions'));
        $response->assertDontSee($transaction->description);
    }

    public function test_create_form_is_accessible()
    {
        $response = $this->get(route('transaction.create'));
        $response->assertStatus(200);
        $response->assertSee('Nova Transação');
    }

    public function test_request_updates_transaction()
    {
        $transaction = Transaction::factory()->create();

        $this->patch(route('transaction.update', $transaction->id), [
            'description' => 'Atualizado',
            'amount' => 55,
            'date' => now()->toDateString(),
            'category' => 'Nova',
        ]);

        $this->assertDatabaseHas('transactions', ['description' => 'Atualizado']);
    }

    public function test_transaction_index_page_contains_create_link()
    {
        $response = $this->get(route('transactions'));
        $response->assertSee(route('transaction.create'));
    }

    public function test_transaction_destroy_route_exists()
    {
        $transaction = Transaction::factory()->create();
        $response = $this->delete(route('transaction.destroy', $transaction->id));
        $response->assertRedirect(route('transactions'));
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }
}
