<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Actor;
use App\Models\Inventory\Outgoing;
use App\Models\Inventory\Product;
use App\Models\Inventory\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class OutgoingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_outgoing_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/outgoing');

        Gate::authorize('haveaccess', 'outgoing.index');

        $outgoings = Outgoing::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['outgoings', 'status']);
    }

    /** @test */
    public function test_outgoing_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/outgoing/create');

        Gate::authorize('haveaccess', 'outgoing.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $outgoing = Outgoing::first();

        $response = $this->getJson('/api/inventory/outgoing/' . $outgoing->id);

        Gate::authorize('haveaccess', 'outgoing.show');

        $response->assertOk();

        $response->assertJsonStructure(['outgoing', 'status']);
    }

    /** @test */
    public function test_outgoing_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => Product::first()->id,
                'cantidad' => 10,
                'precio' => 60,
            ],
            [
                'product_id' => Product::latest('id')->first()->id,
                'cantidad' => 3,
                'precio' => 30,
            ]
        ];
        $subtotal = 690;
        $taxes = 82.80;
        $total = 772.80;

        $response = $this->postJson('/api/inventory/outgoing', [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.create');

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = Outgoing::latest('id')->first();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($outgoing->subtotal, $subtotal);
        $this->assertEquals($outgoing->taxes, $taxes);
        $this->assertEquals($outgoing->total, $total);

        $response->assertJsonStructure([
            'status',
            'message',
            'outgoing'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $outgoing = Outgoing::first();

        $response = $this->getJson('/api/inventory/outgoing/' . $outgoing->id . '/edit');

        Gate::authorize('haveaccess', 'outgoing.edit');

        $response->assertOk();

        $response->assertJsonStructure(['outgoing', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $outgoing = Outgoing::first();

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => Product::first()->id,
                'cantidad' => 10,
                'precio' => 20,
            ],
            [
                'product_id' => Product::latest('id')->first()->id,
                'cantidad' => 3,
                'precio' => 40,
            ]
        ];
        $subtotal = 320;
        $taxes = 38.40;
        $total = 358.40;

        $response = $this->putJson('/api/inventory/outgoing/' . $outgoing->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.edit');

        $response->assertOk();

        $this->assertCount(1, Outgoing::all());

        $outgoing = $outgoing->fresh();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($outgoing->subtotal, $subtotal);
        $this->assertEquals($outgoing->taxes, $taxes);
        $this->assertEquals($outgoing->total, $total);

        $response->assertJsonStructure(['status', 'message', 'outgoing'])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $outgoing = Outgoing::first();

        $response = $this->deleteJson('/api/inventory/outgoing/' . $outgoing->id);

        Gate::authorize('haveaccess', 'outgoing.destroy');

        $response->assertOk();

        $this->assertCount(0, Outgoing::all());

        $outgoings = Outgoing::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'outgoings'])->assertStatus(200);
    }
}
