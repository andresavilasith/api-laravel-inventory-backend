<?php

namespace Tests\Feature\Inventory;

use App\Helpers\DefaultDataSeed;
use App\Models\Inventory\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function user_login()
    {
        DefaultDataSeed::default_data_seed();

        $user = User::first();

        Passport::actingAs($user);
    }

    /** @test */
    public function test_document_index()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/document');

        Gate::authorize('haveaccess', 'document.index');

        $documents = Document::paginate(15);

        $response->assertOk();

        $response->assertJsonStructure(['documents', 'status']);
    }

    /** @test */
    public function test_document_create()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $response = $this->getJson('/api/inventory/document/create');

        Gate::authorize('haveaccess', 'document.create');

        $response->assertOk();

        $response->assertJsonStructure([
            'status'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_document_show()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $document = Document::first();

        $response = $this->getJson('/api/inventory/document/' . $document->id);

        Gate::authorize('haveaccess', 'document.show');

        $response->assertOk();

        $response->assertJsonStructure(['document', 'status']);
    }

    /** @test */
    public function test_document_store()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $name = "Cedula";

        $response = $this->postJson('/api/inventory/document', [
            'name' => $name
        ]);

        Gate::authorize('haveaccess', 'document.create');

        $response->assertOk();

        $this->assertCount(2, Document::all());

        $document = Document::latest('id')->first();

        $this->assertEquals($document->name, $name);

        $response->assertJsonStructure([
            'status',
            'message'
        ])->assertStatus(200);
    }

    /** @test */
    public function test_document_edit()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $document = Document::first();


        $response = $this->getJson('/api/inventory/document/' . $document->id . '/edit');

        Gate::authorize('haveaccess', 'document.edit');

        $response->assertOk();

        $response->assertJsonStructure(['document', 'status'])->assertStatus(200);
    }

    /** @test */
    public function test_document_update()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $document = Document::first();

        $name = 'Ruc';

        $response = $this->putJson('/api/inventory/document/' . $document->id, [
            'name' => $name,
        ]);

        Gate::authorize('haveaccess', 'document.edit');

        $response->assertOk();

        $this->assertCount(1, Document::all());

        $document = $document->fresh();

        $this->assertEquals($document->name, $name);

        $response->assertJsonStructure(['status', 'message', 'document'])->assertStatus(200);
    }

    /** @test */
    public function test_document_destroy()
    {
        $this->withoutExceptionHandling();

        $this->user_login();

        $document = Document::first();

        $response = $this->deleteJson('/api/inventory/document/' . $document->id);

        Gate::authorize('haveaccess', 'document.destroy');

        $response->assertOk();

        $this->assertCount(0, Document::all());

        $documents = Document::paginate(15);

        $response->assertJsonStructure(['status', 'message', 'documents'])->assertStatus(200);
    }
}
