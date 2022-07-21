<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Classification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class ClassificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_classification_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/classification');

        Gate::authorize('haveaccess', 'classification.index');

        $classifications = Classification::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['classifications', 'status']);
    }

    /** @test */
    public function test_classification_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/classification/create');

        Gate::authorize('haveaccess', 'classification.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_classification_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $classification = Classification::first();


        $response = $this->getJson('/api/inventory/classification/' . $classification->id . '/edit');

        Gate::authorize('haveaccess', 'classification.edit');

        $response->assertOk();

        $response->assertJsonStructure(['classification', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_classification_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $classification = Classification::first();

        $response = $this->getJson('/api/inventory/classification/' . $classification->id);

        Gate::authorize('haveaccess', 'classification.show');

        $response->assertOk();

        $response->assertJsonStructure(['classification', 'status']);
    }

    /** @test */
    public function test_classification_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $name = "Clasificacion nueva";
        $description = "Clasificacion descripcion";

        
        $response = $this->postJson('/api/inventory/classification', [
            'name' => $name,
            'description' => $description
        ]);
        
        Gate::authorize('haveaccess', 'classification.create');

        $response->assertOk();

        $this->assertCount(3, Classification::all());

        $classification = Classification::latest('id')->first();

        $this->assertEquals($classification->name, $name);
        $this->assertEquals($classification->description, $description);

        $response->assertJsonStructure([
            'classification',
            'status',
            'message'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_classification_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $classification = Classification::first();

        $name = 'Clasificacion editada';
        $description = 'Descripcion editada';

        $response = $this->putJson('/api/inventory/classification/' . $classification->id, [
            'name' => $name,
            'description' => $description,
        ]);

        Gate::authorize('haveaccess', 'classification.edit');

        $response->assertOk();

        $this->assertCount(2, Classification::all());

        $classification = $classification->fresh();

        $this->assertEquals($classification->name, $name);
        $this->assertEquals($classification->description, $description);

        $response->assertJsonStructure(['status', 'message', 'classification'])->assertStatus(200);
    }

    /** @test */
    public function test_classification_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $classification = Classification::first();

        $response = $this->deleteJson('/api/inventory/classification/' . $classification->id);

        Gate::authorize('haveaccess', 'classification.destroy');

        $response->assertOk();

        $this->assertCount(1, Classification::all());

        $classifications = Classification::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'classifications'])->assertStatus(200);
    }
}
