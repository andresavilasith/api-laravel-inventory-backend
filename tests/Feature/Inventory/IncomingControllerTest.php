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
                'price' => 10,
                'stock' => 5,
                'receipts' => 20,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'price' => 15,
                'stock' => 5,
                'receipts' => 15,
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price, //10
                'quantity' => 10,
            ],
            [
                'product_id' => $product2->id,
                'price' => $product2->price, //15
                'quantity' => 30,
            ],

        ];
        $total = 550;

        $response = $this->postJson('/api/inventory/incoming', [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        Gate::authorize('haveaccess', 'incoming.create');

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = Incoming::latest('id')->first();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1->stock, 15);
        $this->assertEquals($product1->receipts, 30);
        $this->assertEquals($product2->stock, 35);
        $this->assertEquals($product2->receipts, 45);
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
                'price' => 10,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'price' => 15,
                'stock' => 13,
                'receipts' => 12,
            ]
        );

        $incoming = Incoming::factory()->create(
            [
                'products' => [
                    [
                        'product_id' => $product1->id,
                        'price' => $product1->price, //10
                        'quantity' => 15,
                    ],
                    [
                        'product_id' => $product2->id,
                        'price' => $product2->price, //15
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
                'price' => $product1->price, //10
                'quantity' => 4,
            ],
            [
                'product_id' => $product2->id,
                'price' => $product2->price, //15
                'quantity' => 6,
            ],
        ];
        $total = 130;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);



        Gate::authorize('haveaccess', 'incoming.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1->stock, 7);
        $this->assertEquals($product1->receipts, 5);
        $this->assertEquals($product2->stock, 9);
        $this->assertEquals($product2->receipts, 8);
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
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'price' => 15
            ]
        );

        $incoming = Incoming::factory()->create(
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
                        'quantity' => 5,
                    ]
                ]
            ]
        );

        $transaction_id = Transaction::first()->id;
        $actor_id = Actor::first()->id;
        $products = [
            [
                'product_id' => $product1->id,
                'price' => $product1->price, //10
                'quantity' => 17,
            ],
            [
                'product_id' => $product2->id,
                'price' => $product2->price, //15
                'quantity' => 9,
            ],
        ];
        $total = 305;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'incoming.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1->stock, 20);
        $this->assertEquals($product1->receipts, 18);
        $this->assertEquals($product2->stock, 17);
        $this->assertEquals($product2->receipts, 16);
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
                'price' => 10
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
                'price' => 15
            ]
        );

        $incoming = Incoming::factory()->create(
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
                'price' => $product1->price, //10
                'quantity' => 4,
            ],
        ];
        $total = 40;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);


        Gate::authorize('haveaccess', 'incoming.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1->stock, 7);
        $this->assertEquals($product1->receipts, 5);
        $this->assertEquals($product2->stock, 3);
        $this->assertEquals($product2->receipts, 2);
        $this->assertEquals($incoming->total, $total);

        $response->assertJsonStructure(['status', 'message', 'incoming'])->assertStatus(200);
    }

    /** @test */
    public function test_incoming_update_with_more_items()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product1 = Product::factory()->create(
            [
                'stock' => 18,
                'price' => 10,
                'receipts' => 16,
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'price' => 15,
                'receipts' => 12,
            ]
        );
        $product3 = Product::factory()->create(
            [
                'stock' => 23,
                'price' => 20,
                'receipts' => 15,
            ]
        );
        $product4 = Product::factory()->create(
            [
                'stock' => 16,
                'price' => 25,
                'receipts' => 12,
            ]
        );

        $incoming = Incoming::factory()->create(
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
                'price' => $product1->price,//10
                'quantity' => 4,
            ],
            [
                'product_id' => $product3->id,
                'price' => $product3->price,//20
                'quantity' => 7,
            ],
            [
                'product_id' => $product4->id,
                'price' => $product4->price,//25
                'quantity' => 14,
            ],
        ];
        $total = 530;

        $response = $this->putJson('/api/inventory/incoming/' . $incoming->id, [
            'transaction_id' => $transaction_id,
            'actor_id' => $actor_id,
            'products' => $products,
            'total' => $total,
        ]);

        Gate::authorize('haveaccess', 'incoming.edit');

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();
        $product3 = $product3->fresh();
        $product4 = $product4->fresh();

        $response->assertOk();

        $this->assertCount(2, Incoming::all());

        $incoming = $incoming->fresh();

        $this->assertEquals($incoming->transaction_id, $transaction_id);
        $this->assertEquals($incoming->actor_id, $actor_id);
        $this->assertEquals($incoming->products, $products);
        $this->assertEquals($product1->stock, 7);
        $this->assertEquals($product1->receipts, 5);
        $this->assertEquals($product2->stock, 3);
        $this->assertEquals($product2->receipts, 2);
        $this->assertEquals($product3->stock, 30);
        $this->assertEquals($product3->receipts, 22);
        $this->assertEquals($product4->stock, 30);
        $this->assertEquals($product4->receipts, 26);
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
            ]
        );
        $product2 = Product::factory()->create(
            [
                'stock' => 13,
                'receipts' => 12,
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

        $product1 = $product1->fresh();
        $product2 = $product2->fresh();

        $this->assertEquals($product1->stock, 13);
        $this->assertEquals($product1->receipts, 11);
        $this->assertEquals($product2->stock, 6);
        $this->assertEquals($product2->receipts, 5);

        $incomings = Incoming::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'incomings'])->assertStatus(200);
    }
}
