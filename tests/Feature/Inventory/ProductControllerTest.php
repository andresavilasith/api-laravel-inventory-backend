<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Classification;
use App\Models\Inventory\Product;
use App\Models\Inventory\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_product_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/product');

        Gate::authorize('haveaccess', 'product.index');

        $products = Product::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['products', 'status']);
    }

    /** @test */
    public function test_product_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/product/create');

        Gate::authorize('haveaccess', 'product.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_product_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product = Product::first();

        $response = $this->getJson('/api/inventory/product/' . $product->id);

        Gate::authorize('haveaccess', 'product.show');

        $response->assertOk();

        $response->assertJsonStructure(['product', 'status']);
    }

    /** @test */
    public function test_product_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $classification_id = Classification::first()->id;
        $tax_id = Tax::first()->id;
        $code = 'p01';
        $name = 'Producto nuevo';
        $image = null;
        $price = 50.20;
        $stock = 9;
        $sales = 1;
        $receipts = 10;

        $response = $this->postJson('/api/inventory/product', [
            'classification_id' => $classification_id,
            'tax_id' => $tax_id,
            'code' => $code,
            'name' => $name,
            'image' => $image,
            'price' => $price,
            'stock' => $stock,
            'sales' => $sales,
            'receipts' => $receipts,
        ]);

        Gate::authorize('haveaccess', 'product.create');

        $response->assertOk();

        $this->assertCount(2, Product::all());

        $product = Product::latest('id')->first();

        $this->assertEquals($product->classification_id, $classification_id);
        $this->assertEquals($product->tax_id, $tax_id);
        $this->assertEquals($product->code, $code);
        $this->assertEquals($product->name, $name);
        $this->assertEquals($product->image, $image);
        $this->assertEquals($product->price, $price);
        $this->assertEquals($product->stock, $stock);
        $this->assertEquals($product->sales, $sales);
        $this->assertEquals($product->receipts, $receipts);

        $response->assertJsonStructure([
            'status',
            'message',
            'product'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_product_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product = Product::first();

        $response = $this->getJson('/api/inventory/product/' . $product->id . '/edit');

        Gate::authorize('haveaccess', 'product.edit');

        $response->assertOk();

        $response->assertJsonStructure(['product', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_product_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product = Product::first();

        $classification_id = Classification::first()->id;
        $tax_id = Tax::first()->id;
        $code = 'pr002';
        $name = 'Producto editado';
        $image = null;
        $price = 40.20;
        $stock = 7;
        $sales = 3;
        $receipts = 10;

        $response = $this->putJson('/api/inventory/product/' . $product->id, [
            'classification_id' => $classification_id,
            'tax_id' => $tax_id,
            'code' => $code,
            'name' => $name,
            'image' => $image,
            'price' => $price,
            'stock' => $stock,
            'sales' => $sales,
            'receipts' => $receipts,
        ]);

        Gate::authorize('haveaccess', 'product.edit');

        $response->assertOk();

        $this->assertCount(1, Product::all());

        $product = $product->fresh();

        $this->assertEquals($product->classification_id, $classification_id);
        $this->assertEquals($product->tax_id, $tax_id);
        $this->assertEquals($product->code, $code);
        $this->assertEquals($product->name, $name);
        $this->assertEquals($product->image, $image);
        $this->assertEquals($product->price, $price);
        $this->assertEquals($product->stock, $stock);
        $this->assertEquals($product->sales, $sales);
        $this->assertEquals($product->receipts, $receipts);

        $response->assertJsonStructure(['status', 'message', 'product'])->assertStatus(200);
    }

    /** @test */
    public function test_product_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $product = Product::first();

        $response = $this->deleteJson('/api/inventory/product/' . $product->id);

        Gate::authorize('haveaccess', 'product.destroy');

        $response->assertOk();

        $this->assertCount(0, Product::all());

        $products = Product::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'products'])->assertStatus(200);
    }
}
