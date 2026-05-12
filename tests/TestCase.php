<?php

namespace Tests;

use App\Models\CategoryMenu;
use App\Models\Component;
use App\Models\Ingridient;
use App\Models\IngridientType;
use App\Models\Inventory;
use App\Models\Menu;
use App\Models\Role;
use App\Models\Table;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Create a user with the given role name (role row created if missing). */
    protected function makeUser(string $roleName): User
    {
        $role = Role::firstOrCreate(['name' => $roleName]);
        $u    = uniqid();

        return User::create([
            'name'     => $roleName.' User',
            'username' => strtolower($roleName).'_'.$u,
            'email'    => strtolower($roleName).'_'.$u.'@test.local',
            'password' => bcrypt('password'),
            'roles_id' => $role->id,
        ]);
    }

    /** Create an available table. */
    protected function makeTable(): Table
    {
        return Table::create([
            'name'     => 'T-'.uniqid(),
            'capacity' => 4,
            'status'   => 'available',
        ]);
    }

    /**
     * Create a menu priced at 10_000 with a one-ingredient recipe:
     * ingredient stocked at 100 units, recipe consumes 2 units per menu sold.
     */
    protected function makeMenuWithRecipe(): Menu
    {
        $cat  = CategoryMenu::firstOrCreate(['name' => 'Main']);
        $menu = Menu::create([
            'name'             => 'Nasi Goreng '.uniqid(),
            'availability'     => 1,
            'price'            => 10000,
            'categoryMenus_id' => $cat->id,
        ]);

        $type = IngridientType::firstOrCreate(['name' => 'Dry']);
        $ing  = Ingridient::create([
            'name'                => 'Rice '.uniqid(),
            'unit'                => 'g',
            'cost_per_unit'       => 10,
            'min_stock'           => 10,
            'ingridient_types_id' => $type->id,
        ]);
        Inventory::create([
            'ingridient_id'    => $ing->id,
            'quantity_on_hand' => 100,
            'last_updated'     => now(),
        ]);
        Component::create([
            'menus_id'       => $menu->id,
            'ingridients_id' => $ing->id,
            'quantity'       => 2,
        ]);

        return $menu;
    }
}
