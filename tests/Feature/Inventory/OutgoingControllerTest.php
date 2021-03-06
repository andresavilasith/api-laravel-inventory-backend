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
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 15,
                'price' => 15
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price,
                'quantity' => 10,
            ],
            [
                'product_id' => $product2->id,
                'price' => $product2->price,
                'quantity' => 13,
            ]
        ];
        $subtotal = 295;
        $taxes = 35.40;
        $total = 330.40;

        $response = $this->postJson('/api/inventory/outgoing', [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.create');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = Outgoing::latest('id')->first();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1->stock, 15);
        $this->assertEquals($product1->sales, 30);
        $this->assertEquals($product2->stock, 22);
        $this->assertEquals($product2->sales, 28);
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
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 18,
                'price' => 15
            ]
        );

        $outgoing = Outgoing::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'price' => $product1->price,
                        'quantity' => 15,
                    ],
                    [
                        'product_id' => $product2->id,
                        'price' => $product2->price,
                        'quantity' => 10,
                    ]
                ]
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price,
                'quantity' => 12,
            ],
            [
                'product_id' => $product2->id,
                'price' => $product2->price,
                'quantity' => 18,
            ]
        ];
        $subtotal = 390;
        $taxes = 46.80;
        $total = 436.80;

        $response = $this->putJson('/api/inventory/outgoing/' . $outgoing->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = $outgoing->fresh();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1->stock, 26);
        $this->assertEquals($product1->sales, 18);
        $this->assertEquals($product2->stock, 27);
        $this->assertEquals($product2->sales, 26);
        $this->assertEquals($outgoing->subtotal, $subtotal);
        $this->assertEquals($outgoing->taxes, $taxes);
        $this->assertEquals($outgoing->total, $total);

        $response->assertJsonStructure(['status', 'message', 'outgoing'])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_update_with_less_products()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 23,
                'sales' => 21,
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 18,
                'price' => 15
            ]
        );

        $outgoing = Outgoing::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'price' => $product1->price,
                        'quantity' => 15,
                    ],
                    [
                        'product_id' => $product2->id,
                        'price' => $product2->price,
                        'quantity' => 10,
                    ]
                ]
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price,
                'quantity' => 12,
            ]
        ];
        $subtotal = 120;
        $taxes = 14.40;
        $total = 134.40;

        $response = $this->putJson('/api/inventory/outgoing/' . $outgoing->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = $outgoing->fresh();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1->stock, 26);
        $this->assertEquals($product1->sales, 18);
        $this->assertEquals($product2->stock, 45);
        $this->assertEquals($product2->sales, 8);
        $this->assertEquals($outgoing->subtotal, $subtotal);
        $this->assertEquals($outgoing->taxes, $taxes);
        $this->assertEquals($outgoing->total, $total);

        $response->assertJsonStructure(['status', 'message', 'outgoing'])->assertStatus(200);
    }

    /** @test */
    public function test_outgoing_update_with_more_products()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 23,
                'sales' => 21,
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 35,
                'sales' => 18,
                'price' => 15
            ]
        );
        $product3 = Product::factory()->create(
            [
                'stock' => 30,
                'sales' => 12,
                'price' => 20
            ]
        );
        $product4 = Product::factory()->create(
            [
                'stock' => 15,
                'sales' => 6,
                'price' => 25
            ]
        );

        $outgoing = Outgoing::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'price' => $product1->price,
                        'quantity' => 15,
                    ],
                    [
                        'product_id' => $product2->id,
                        'price' => $product2->price,
                        'quantity' => 10,
                    ]
                ]
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price,
                'quantity' => 12,
            ],
            [
                'product_id' => $product3->id,
                'price' => $product3->price,
                'quantity' => 13,
            ],
            [
                'product_id' => $product4->id,
                'price' => $product4->price,
                'quantity' => 10,
            ],
        ];
        $subtotal = 630;
        $taxes = 75.60;
        $total = 705.60;

        $response = $this->putJson('/api/inventory/outgoing/' . $outgoing->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'subtotal' => $subtotal,
            'taxes' => $taxes,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'outgoing.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();
        $product3 = $product3->fresh();
        $product4 = $product4->fresh();

        $response->assertOk();

        $this->assertCount(2, Outgoing::all());

        $outgoing = $outgoing->fresh();

        $this->assertEquals($outgoing->transaction_id, $transaction_id);
        $this->assertEquals($outgoing->actor_id, $actor_id);
        $this->assertEquals($outgoing->products, $products);
        $this->assertEquals($product1->stock, 26);
        $this->assertEquals($product1->sales, 18);
        $this->assertEquals($product2->stock, 45);
        $this->assertEquals($product2->sales, 8);
        $this->assertEquals($product3->stock, 17);
        $this->assertEquals($product3->sales, 25);
        $this->assertEquals($product4->stock, 5);
        $this->assertEquals($product4->sales, 16);
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

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $this->assertEquals($product1->stock, 23);
        $this->assertEquals($product1->sales, 11);
        $this->assertEquals($product2->stock, 20);
        $this->assertEquals($product2->sales, 5);

        $outgoings = Outgoing::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'outgoings'])->assertStatus(200);
    }
}
