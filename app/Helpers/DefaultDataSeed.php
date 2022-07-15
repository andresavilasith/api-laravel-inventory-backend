<?php

namespace App\Helpers;

use App\Models\Inventory\Actor;
use App\Models\Inventory\CategoryProduct;
use App\Models\Inventory\Classification;
use App\Models\Inventory\Document;
use App\Models\Inventory\Incoming;
use App\Models\Inventory\Outgoing;
use App\Models\Inventory\Product;
use App\Models\Inventory\Tax;
use App\Models\Inventory\Transaction;
use Illuminate\Support\Facades\DB;
use App\Models\Role_User\Role;
use App\Models\Role_User\Category;
use App\Models\Role_User\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DefaultDataSeed
{
    use RefreshDatabase;

    public static function default_data_seed()
    {
        User::factory()->create([
            'name' => 'super admin',
            'email' => 'admin@admin.com',
            'password' => bcrypt('1234')
        ]);


        Role::factory()->create(
            [
                'name' => 'admin',
                'slug' => 'admin',
                'description' => 'User admin',
                'full_access' => 'yes'
            ]
        );


        $role = Role::first();

        Category::factory()->times(2)->create();



        Permission::factory()->times(5)->create();

        $permissions = Permission::all();



        // Populate the pivot table
        User::all()->each(function ($user) use ($role) {
            $user->roles()->sync(
                $role->id
            );
        });


        // Populate the pivot table
        Role::all()->each(function ($role) use ($permissions) {
            $role->permissions()->sync(
                $permissions
            );
        });

        Tax::factory()->create();
        Document::factory()->create();
        Classification::factory()->create();
        Transaction::factory()->create();
        Actor::factory()->create();
        Product::factory()->create();
        Incoming::factory()->create();
        Outgoing::factory()->create();
    }
}
