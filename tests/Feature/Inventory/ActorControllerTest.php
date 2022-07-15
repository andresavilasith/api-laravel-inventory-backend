<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Actor;
use App\Models\Inventory\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

use function GuzzleHttp\Promise\all;

class ActorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_actor_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/actor');

        Gate::authorize('haveaccess', 'actor.index');

        $actors = Actor::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['actors', 'status']);
    }

    /** @test */
    public function test_actor_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/actor/create');

        Gate::authorize('haveaccess', 'actor.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_actor_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $actor = Actor::first();

        $response = $this->getJson('/api/inventory/actor/' . $actor->id);

        Gate::authorize('haveaccess', 'actor.show');

        $response->assertOk();

        $response->assertJsonStructure(['actor', 'status']);
    }

    /** @test */
    public function test_actor_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $document_id = Document::first()->id;
        $document = '0106562862';
        $name = "Cliente Nuevo";
        $client = 1;
        $provider = 0;
        $address = 'Calle 1';
        $email = 'cliente@gmail.com';
        $cellphone = '0982541255';
        $phone = '072541255';

        $response = $this->postJson('/api/inventory/actor', [
            'document_id' => $document_id,
            'document' => $document,
            'name' => $name,
            'client' => $client,
            'provider' => $provider,
            'address' => $address,
            'email' => $email,
            'cellphone' => $cellphone,
            'phone' => $phone,
        ]);

        Gate::authorize('haveaccess', 'actor.create');

        $response->assertOk();

        $this->assertCount(4, Actor::all());

        $actor = Actor::latest('id')->first();

        $this->assertEquals($actor->name, $name);

        $response->assertJsonStructure([
            'status',
            'message',
            'actor'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_actor_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $actor = Actor::first();

        $response = $this->getJson('/api/inventory/actor/' . $actor->id . '/edit');

        Gate::authorize('haveaccess', 'actor.edit');

        $response->assertOk();

        $response->assertJsonStructure(['actor', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_actor_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $actor = Actor::first();

        $document_id = Document::latest('id')->first()->id;;

        $document_id = $document_id;
        $document = '010656252';
        $name = "Cliente Editado";
        $client = 1;
        $provider = 1;
        $address = 'Calle editada';
        $email = 'clienteeditado@gmail.com';
        $cellphone = '09828241255';
        $phone = '0725489255';

        $response = $this->putJson('/api/inventory/actor/' . $actor->id, [
            'document_id' => $document_id,
            'document' => $document,
            'name' => $name,
            'client' => $client,
            'provider' => $provider,
            'address' => $address,
            'email' => $email,
            'cellphone' => $cellphone,
            'phone' => $phone,
        ]);

        Gate::authorize('haveaccess', 'actor.edit');

        $response->assertOk();

        $this->assertCount(3, Actor::all());

        $actor = $actor->fresh();

        $this->assertEquals($actor->name, $name);

        $response->assertJsonStructure(['status', 'message', 'actor'])->assertStatus(200);
    }

    /** @test */
    public function test_actor_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $actor = Actor::first();

        $response = $this->deleteJson('/api/inventory/actor/' . $actor->id);

        Gate::authorize('haveaccess', 'actor.destroy');

        $response->assertOk();

        $this->assertCount(2, Actor::all());

        $actors = Actor::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'actors'])->assertStatus(200);
    }
}
