<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_transaction_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/transaction');

        Gate::authorize('haveaccess', 'transaction.index');

        $transactions = Transaction::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['transactions', 'status']);
    }

    /** @test */
    public function test_transaction_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/transaction/create');

        Gate::authorize('haveaccess', 'transaction.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_transaction_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $transaction = Transaction::first();

        $response = $this->getJson('/api/inventory/transaction/' . $transaction->id);

        Gate::authorize('haveaccess', 'transaction.show');

        $response->assertOk();

        $response->assertJsonStructure(['transaction', 'status']);
    }

    /** @test */
    public function test_transaction_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $code = "01Fact";
        $name = "Factura";
        $description = "Documento tributario";
        $type = "outgoing";

        $response = $this->postJson('/api/inventory/transaction', [
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'type' => $type,
        ]);


        Gate::authorize('haveaccess', 'transaction.create');

        $response->assertOk();

        $this->assertCount(2, Transaction::all());

        $transaction = Transaction::latest('id')->first();

        $this->assertEquals($transaction->name, $name);

        $response->assertJsonStructure([
            'status',
            'message',
            'transaction'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_transaction_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $transaction = Transaction::first();


        $response = $this->getJson('/api/inventory/transaction/' . $transaction->id . '/edit');

        Gate::authorize('haveaccess', 'transaction.edit');

        $response->assertOk();

        $response->assertJsonStructure(['transaction', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_transaction_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $transaction = Transaction::first();

        $code = '02NT';
        $name = 'Nota de venta';
        $description = 'Nota de venta descripcion';
        $type = 'incoming';

        $response = $this->putJson('/api/inventory/transaction/' . $transaction->id, [
            'code' => $code,
            'name' => $name,
            'description' => $description,
            'type' => $type,
        ]);

        Gate::authorize('haveaccess', 'transaction.edit');

        $response->assertOk();

        $this->assertCount(1, Transaction::all());

        $transaction = $transaction->fresh();

        $this->assertEquals($transaction->name, $name);

        $response->assertJsonStructure(['status', 'message', 'transaction'])->assertStatus(200);
    }

    /** @test */
    public function test_transaction_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $transaction = Transaction::first();

        $response = $this->deleteJson('/api/inventory/transaction/' . $transaction->id);

        Gate::authorize('haveaccess', 'transaction.destroy');

        $response->assertOk();

        $this->assertCount(0, Transaction::all());

        $transactions = Transaction::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'transactions'])->assertStatus(200);
    }
}
