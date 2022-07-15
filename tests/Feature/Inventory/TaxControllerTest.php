<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Tax;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class TaxControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_tax_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/tax');

        Gate::authorize('haveaccess', 'tax.index');

        $taxes = Tax::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['taxes', 'status']);
    }

    /** @test */
    public function test_tax_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/tax/create');

        Gate::authorize('haveaccess', 'tax.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_tax_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $tax = Tax::first();


        $response = $this->getJson('/api/inventory/tax/' . $tax->id . '/edit');

        Gate::authorize('haveaccess', 'tax.edit');

        $response->assertOk();

        $response->assertJsonStructure(['tax', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_tax_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $tax = Tax::first();

        $response = $this->getJson('/api/inventory/tax/' . $tax->id);

        Gate::authorize('haveaccess', 'tax.show');

        $response->assertOk();

        $response->assertJsonStructure(['tax', 'status']);
    }

    /** @test */
    public function test_tax_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $name = "Iva";
        $percentage = 12;

        $response = $this->postJson('/api/inventory/tax', [
            'name' => $name,
            'percentage' => $percentage
        ]);

        Gate::authorize('haveaccess', 'tax.create');

        $response->assertOk();

        $this->assertCount(3, Tax::all());

        $tax = Tax::latest('id')->first();

        $this->assertEquals($tax->name, $name);
        $this->assertEquals($tax->percentage, $percentage);

        $response->assertJsonStructure([
            'status',
            'message'
        ])->assertStatus(200);
    }

    public function test_tax_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $tax = Tax::first();

        $name = 'Rise';
        $percentage = 12;

        $response = $this->putJson('/api/inventory/tax/' . $tax->id, [
            'name' => $name,
            'percentage' => $percentage
        ]);

        Gate::authorize('haveaccess', 'tax.edit');

        $response->assertOk();

        $this->assertCount(2, Tax::all());

        $tax = $tax->fresh();

        $this->assertEquals($tax->name, $name);
        $this->assertEquals($tax->percentage, $percentage);

        $response->assertJsonStructure(['status', 'message'])->assertStatus(200);
    }

    public function test_tax_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $tax = Tax::first();

        $response = $this->deleteJson('/api/inventory/tax/' . $tax->id);

        Gate::authorize('haveaccess', 'tax.destroy');

        $response->assertOk();

        $this->assertCount(1, Tax::all());

        $taxes = Tax::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'taxes'])->assertStatus(200);
    }
}
