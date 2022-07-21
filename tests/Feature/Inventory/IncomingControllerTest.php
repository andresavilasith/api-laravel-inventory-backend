<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Actor;
use App\Models\Inventory\Incoming;
use App\Models\Inventory\Product;
use App\Models\Inventory\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class IncomingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_incoming_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/incoming');

        Gate::authorize('haveaccess', 'incoming.index');

        $incomings = Incoming::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['incomings', 'status']);
    }

    /** @test */
    public function test_incoming_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/incoming/create');

        Gate::authorize('haveaccess', 'incoming.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $incoming = Incoming::first();

        $response = $this->getJson('/api/inventory/incoming/' . $incoming->id);

        Gate::authorize('haveaccess', 'incoming.show');

        $response->assertOk();

        $response->assertJsonStructure(['incoming', 'status']);
    }

    /** @test */
    public function test_incoming_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 5,
                'receipts' => 20,
                'sales' => 0,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 5,
                'receipts' => 15,
                'sales' => 0,
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
                'quantity' => 30,
                'price' => 30,
            ],

        ];
        $total = 1500;

        $response = $this->postJson('/api/inventory/incoming', [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);

        //dd($response->getContent());

        $product1Fresh = Product::find($product1->id);
        $product2Fresh = Product::find($product2->id);

        Gate::authorize('haveaccess', 'incoming.create');

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = Incoming::latest('id')->first();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1Fresh->stock, 15);
        $this->assertEquals($product1Fresh->receipts, 30);
        $this->assertEquals($product2Fresh->stock, 35);
        $this->assertEquals($product2Fresh->receipts, 45);
        $this->assertEquals($incoming->total, $total);

        $response->assertJsonStructure([
            'status',
            'message',
            'incoming'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $incoming = Incoming::first();

        $response = $this->getJson('/api/inventory/incoming/' . $incoming->id . '/edit');

        Gate::authorize('haveaccess', 'incoming.edit');

        $response->assertOk();

        $response->assertJsonStructure(['incoming', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'receipts' => 16,
                'sales' => 0,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'sales' => 0,
            ]
        );

        $incoming = Incoming::factory()->create(
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
                'quantity' => 4,
                'price' => 60,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 6,
                'price' => 60,
            ],
        ];
        $total = 600;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);
      


        Gate::authorize('haveaccess', 'incoming.edit');

        $product1Fresh = Product::find($product1->id);
        $product2Fresh = Product::find($product2->id);

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1Fresh->stock, 7);
        $this->assertEquals($product1Fresh->receipts, 5);
        $this->assertEquals($product2Fresh->stock, 9);
        $this->assertEquals($product2Fresh->receipts, 8);
        $this->assertEquals($incoming->total, $total);

        $response->assertJsonStructure(['status', 'message', 'incoming'])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_update_with_more_quantity()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'receipts' => 16,
                'sales' => 0,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'sales' => 0,
            ]
        );

        $incoming = Incoming::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'quantity' => 15,
                        'price' => 50,
                    ],
                    [
                        'product_id' => $product2->id,
                        'quantity' => 5,
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
                'quantity' => 17,
                'price' => 60,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 9,
                'price' => 60,
            ],
        ];
        $total = 1560;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);


        Gate::authorize('haveaccess', 'incoming.edit');

        $product1Fresh = Product::find($product1->id);
        $product2Fresh = Product::find($product2->id);

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1Fresh->stock, 20);
        $this->assertEquals($product1Fresh->receipts, 18);
        $this->assertEquals($product2Fresh->stock, 17);
        $this->assertEquals($product2Fresh->receipts, 16);
        $this->assertEquals($incoming->total, $total);

        $response->assertJsonStructure(['status', 'message', 'incoming'])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_update_with_less_items()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'receipts' => 16,
                'sales' => 0,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'sales' => 0,
            ]
        );

        $incoming = Incoming::factory()->create(
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
                'quantity' => 4,
                'price' => 60,
            ],
        ];
        $total = 240;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);


        Gate::authorize('haveaccess', 'incoming.edit');

        $product1Fresh = Product::find($product1->id);
        $product2Fresh = Product::find($product2->id);

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1Fresh->stock, 7);
        $this->assertEquals($product1Fresh->receipts, 5);
        $this->assertEquals($product2Fresh->stock, 3);
        $this->assertEquals($product2Fresh->receipts, 2);
        $this->assertEquals($incoming->total, $total);

        $response->assertJsonStructure(['status', 'message', 'incoming'])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'receipts' => 16,
                'sales' => 0,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'sales' => 0,
            ]
        );

        $incoming = Incoming::factory()->create(
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

        $response = $this->deleteJson('/api/inventory/incoming/' . $incoming->id);

        Gate::authorize('haveaccess', 'incoming.destroy');

        $response->assertOk();

        $this->assertCount(1, Incoming::all());

        $product1Fresh = Product::find($product1->id);
        $product2Fresh = Product::find($product2->id);

        $this->assertEquals($product1Fresh->stock, 13);
        $this->assertEquals($product1Fresh->receipts, 11);
        $this->assertEquals($product2Fresh->stock, 6);
        $this->assertEquals($product2Fresh->receipts, 5);

        $incomings = Incoming::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'incomings'])->assertStatus(200);
    }
}
