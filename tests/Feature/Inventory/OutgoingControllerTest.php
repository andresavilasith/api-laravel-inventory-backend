<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Actor;
use App\Models\Inventory\Outgoing;
use App\Models\Inventory\Product;
use App\Models\Inventory\Tax;
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

        $product1 = Product::factory()->create(
            [
                'stock' => 25,
                'sales' => 20,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 15,
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'quantity' => 10,
                'price' => 60,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 13,
                'price' => 30,
            ]
        ];
        $subtotal = 990;
        $taxes = 118.80;
        $total = 1108.80;

        $response = $this->postJson('/api/inventory/outgoing', [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.create');

        $product1Fresh = $product1->fresh();
        $product2Fresh = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = Outgoing::latest('id')->first();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1Fresh->stock, 15);
        $this->assertEquals($product1Fresh->sales, 30);
        $this->assertEquals($product2Fresh->stock, 22);
        $this->assertEquals($product2Fresh->sales, 28);
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

        $product1 = Product::factory()->create(
            [
                'stock' => 23,
                'sales' => 21,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 18,
            ]
        );

        $outgoing = Outgoing::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 15,
                        'price' => 50,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 10,
                        'price' => 40,
                    ]
                ]
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'quantity' => 12,
                'price' => 20,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 18,
                'price' => 40,
            ]
        ];
        $subtotal = 960;
        $taxes = 115.20;
        $total = 1075.20;

        $response = $this->putJson('/api/inventory/outgoing/' . $outgoing->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.edit');

        $product1Fresh = $product1->fresh();
        $product2Fresh = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = $outgoing->fresh();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1Fresh->stock, 26);
        $this->assertEquals($product1Fresh->sales, 18);
        $this->assertEquals($product2Fresh->stock, 27);
        $this->assertEquals($product2Fresh->sales, 26);
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

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'sales' => 16,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'sales' => 12,
            ]
        );

        $outgoing = Outgoing::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 5,
                        'price' => 50,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 7,
                        'price' => 40,
                    ]
                ]
            ]
        );

        $response = $this->deleteJson('/api/inventory/outgoing/' . $outgoing->id);

        Gate::authorize('haveaccess', 'outgoing.destroy');

        $response->assertOk();

        $this->assertCount(1, Outgoing::all());

        $product1Fresh = $product1->fresh();
        $product2Fresh = $product2->fresh();

        $this->assertEquals($product1Fresh->stock, 23);
        $this->assertEquals($product1Fresh->sales, 11);
        $this->assertEquals($product2Fresh->stock, 20);
        $this->assertEquals($product2Fresh->sales, 5);

        $outgoings = Outgoing::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'outgoings'])->assertStatus(200);
    }
}
