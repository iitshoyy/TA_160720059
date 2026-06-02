<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Kasir', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Chef',  'created_at' => now(), 'updated_at' => now()],
        ]);

        // Users (one per role so every dashboard has a demo login)
        DB::table('users')->insert([
            ['id'=>1,'name'=>'Administrator','username'=>'admin','email'=>'admin@restopos.com','password'=>Hash::make('password'),'roles_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Budi Kasir',  'username'=>'kasir','email'=>'kasir@restopos.com','password'=>Hash::make('password'),'roles_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'Chef Anton',  'username'=>'chef', 'email'=>'chef@restopos.com', 'password'=>Hash::make('password'),'roles_id'=>3,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Employees
        DB::table('employees')->insert([
            ['users_id'=>2,'position'=>'Kasir','phone'=>'08112233001','hire_date'=>'2023-01-15','status'=>'active','created_at'=>now(),'updated_at'=>now()],
            ['users_id'=>3,'position'=>'Chef', 'phone'=>'08112233002','hire_date'=>'2022-06-01','status'=>'active','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Payment types
        DB::table('payment_types')->insert([
            ['id'=>1,'name'=>'Cash',         'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Transfer',     'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'QRIS',         'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Debit/Credit', 'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Category Menus
        DB::table('category_menus')->insert([
            ['id'=>1,'name'=>'Main Course','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Appetizer',  'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'Beverage',   'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Dessert',    'created_at'=>now(),'updated_at'=>now()],
            ['id'=>5,'name'=>'Snack',      'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Menus
        DB::table('menus')->insert([
            // Main Course
            ['id'=>1, 'name'=>'Nasi Goreng Special',   'description'=>'Nasi goreng dengan telur, ayam, dan sayuran segar',         'availability'=>1,'price'=>'35000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2, 'name'=>'Mie Goreng Spesial',    'description'=>'Mie goreng dengan topping ayam dan sayuran',                 'availability'=>1,'price'=>'32000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3, 'name'=>'Ayam Bakar Madu',       'description'=>'Ayam bakar dengan bumbu madu dan rempah pilihan',            'availability'=>1,'price'=>'45000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4, 'name'=>'Ikan Bakar Bumbu Bali', 'description'=>'Ikan segar dibakar dengan bumbu khas Bali',                  'availability'=>1,'price'=>'55000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>5, 'name'=>'Soto Ayam',             'description'=>'Soto ayam kuah bening dengan pelengkap',                     'availability'=>1,'price'=>'28000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>6, 'name'=>'Gado-Gado',             'description'=>'Sayuran segar dengan saus kacang spesial',                   'availability'=>1,'price'=>'25000','categoryMenus_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            // Appetizer
            ['id'=>7, 'name'=>'Lumpia Goreng',         'description'=>'Lumpia renyah isi sayuran dan daging',                       'availability'=>1,'price'=>'18000','categoryMenus_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>8, 'name'=>'Tahu Tempe Goreng',     'description'=>'Tahu dan tempe goreng garing',                               'availability'=>1,'price'=>'15000','categoryMenus_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>9, 'name'=>'Salad Buah',            'description'=>'Campuran buah segar dengan saus mayonaise',                  'availability'=>1,'price'=>'20000','categoryMenus_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            // Beverages
            ['id'=>10,'name'=>'Es Teh Manis',          'description'=>'Teh manis segar dengan es batu',                             'availability'=>1,'price'=>'8000', 'categoryMenus_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>11,'name'=>'Jus Alpukat',           'description'=>'Jus alpukat segar dengan susu dan gula',                     'availability'=>1,'price'=>'18000','categoryMenus_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>12,'name'=>'Es Jeruk',              'description'=>'Jeruk peras segar dengan es batu',                           'availability'=>1,'price'=>'12000','categoryMenus_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>13,'name'=>'Air Mineral',           'description'=>'Air mineral botol 600ml',                                    'availability'=>1,'price'=>'5000', 'categoryMenus_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>14,'name'=>'Kopi Hitam',            'description'=>'Kopi arabika hitam tanpa gula',                              'availability'=>1,'price'=>'15000','categoryMenus_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            // Dessert
            ['id'=>15,'name'=>'Es Krim Vanilla',       'description'=>'Es krim vanilla premium dengan topping cokelat',             'availability'=>1,'price'=>'22000','categoryMenus_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>16,'name'=>'Pisang Goreng Keju',    'description'=>'Pisang goreng dengan taburan keju dan susu kental manis',    'availability'=>1,'price'=>'18000','categoryMenus_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            // Snack
            ['id'=>17,'name'=>'Kentang Goreng',        'description'=>'Kentang goreng crispy dengan saus tomat dan mayo',           'availability'=>1,'price'=>'20000','categoryMenus_id'=>5,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>18,'name'=>'Onion Ring',            'description'=>'Bawang bombay goreng crispy',                                'availability'=>1,'price'=>'22000','categoryMenus_id'=>5,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Tables
        DB::table('tables')->insert([
            ['id'=>1, 'name'=>'Meja A1','capacity'=>2,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2, 'name'=>'Meja A2','capacity'=>2,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
            ['id'=>3, 'name'=>'Meja B1','capacity'=>4,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
            ['id'=>4, 'name'=>'Meja B2','capacity'=>4,'status'=>'occupied', 'created_at'=>now(),'updated_at'=>now()],
            ['id'=>5, 'name'=>'Meja B3','capacity'=>4,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
            ['id'=>6, 'name'=>'Meja C1','capacity'=>6,'status'=>'reserved', 'created_at'=>now(),'updated_at'=>now()],
            ['id'=>7, 'name'=>'Meja C2','capacity'=>6,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
            ['id'=>8, 'name'=>'VIP 1',  'capacity'=>8,'status'=>'available','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Ingredient types
        DB::table('ingridient_types')->insert([
            ['id'=>1,'name'=>'Protein',   'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'Sayuran',   'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'Bumbu',     'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Karbohidrat','created_at'=>now(),'updated_at'=>now()],
            ['id'=>5,'name'=>'Minuman',   'created_at'=>now(),'updated_at'=>now()],
            ['id'=>6,'name'=>'Susu & Dairy','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Suppliers
        DB::table('suppliers')->insert([
            ['id'=>1,'name'=>'CV Sumber Makmur',   'phone'=>'031-55512345','email'=>'sumber@supplier.com',   'address'=>'Jl. Raya Darmo No.10, Surabaya',   'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'name'=>'UD Sayur Segar',     'phone'=>'031-55567890','email'=>'sayursegar@gmail.com',  'address'=>'Pasar Keputran No.45, Surabaya',    'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'name'=>'PT Bumbu Nusantara', 'phone'=>'031-55598765','email'=>'bumbu@nusantara.co.id', 'address'=>'Jl. Kenjeran No.88, Surabaya',      'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4,'name'=>'Toko Sembako Jaya',  'phone'=>'085100112233','email'=>'sembakojaya@gmail.com', 'address'=>'Jl. Gubeng Pojok No.5, Surabaya',   'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Ingredients
        DB::table('ingridients')->insert([
            ['id'=>1, 'name'=>'Ayam Potong',    'unit'=>'kg', 'cost_per_unit'=>'35000','min_stock'=>5, 'ingridient_types_id'=>1,'supplier_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>2, 'name'=>'Telur Ayam',     'unit'=>'kg', 'cost_per_unit'=>'28000','min_stock'=>3, 'ingridient_types_id'=>1,'supplier_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3, 'name'=>'Ikan Kakap',     'unit'=>'kg', 'cost_per_unit'=>'55000','min_stock'=>3, 'ingridient_types_id'=>1,'supplier_id'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>4, 'name'=>'Beras',          'unit'=>'kg', 'cost_per_unit'=>'12000','min_stock'=>20,'ingridient_types_id'=>4,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>5, 'name'=>'Mie Kering',     'unit'=>'kg', 'cost_per_unit'=>'15000','min_stock'=>5, 'ingridient_types_id'=>4,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>6, 'name'=>'Bawang Merah',   'unit'=>'kg', 'cost_per_unit'=>'25000','min_stock'=>2, 'ingridient_types_id'=>3,'supplier_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>7, 'name'=>'Bawang Putih',   'unit'=>'kg', 'cost_per_unit'=>'30000','min_stock'=>2, 'ingridient_types_id'=>3,'supplier_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>8, 'name'=>'Cabai Merah',    'unit'=>'kg', 'cost_per_unit'=>'40000','min_stock'=>2, 'ingridient_types_id'=>3,'supplier_id'=>3,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>9, 'name'=>'Tomat',          'unit'=>'kg', 'cost_per_unit'=>'10000','min_stock'=>2, 'ingridient_types_id'=>2,'supplier_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>10,'name'=>'Kangkung',       'unit'=>'kg', 'cost_per_unit'=>'8000', 'min_stock'=>2, 'ingridient_types_id'=>2,'supplier_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>11,'name'=>'Kacang Panjang', 'unit'=>'kg', 'cost_per_unit'=>'9000', 'min_stock'=>2, 'ingridient_types_id'=>2,'supplier_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>12,'name'=>'Minyak Goreng',  'unit'=>'liter','cost_per_unit'=>'18000','min_stock'=>5,'ingridient_types_id'=>3,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>13,'name'=>'Gula Pasir',     'unit'=>'kg', 'cost_per_unit'=>'14000','min_stock'=>3, 'ingridient_types_id'=>3,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>14,'name'=>'Teh Celup',      'unit'=>'pcs','cost_per_unit'=>'500',  'min_stock'=>50,'ingridient_types_id'=>5,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>15,'name'=>'Kopi Arabika',   'unit'=>'kg', 'cost_per_unit'=>'80000','min_stock'=>1, 'ingridient_types_id'=>5,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>16,'name'=>'Susu UHT',       'unit'=>'liter','cost_per_unit'=>'16000','min_stock'=>3,'ingridient_types_id'=>6,'supplier_id'=>4,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>17,'name'=>'Alpukat',        'unit'=>'kg', 'cost_per_unit'=>'20000','min_stock'=>3, 'ingridient_types_id'=>2,'supplier_id'=>2,'created_at'=>now(),'updated_at'=>now()],
            ['id'=>18,'name'=>'Kentang',        'unit'=>'kg', 'cost_per_unit'=>'12000','min_stock'=>3, 'ingridient_types_id'=>4,'supplier_id'=>2,'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Inventory (stock levels)
        DB::table('inventories')->insert([
            ['ingridient_id'=>1,  'quantity_on_hand'=>12.5,'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>2,  'quantity_on_hand'=>8.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>3,  'quantity_on_hand'=>0.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()], // sold-out demo
            ['ingridient_id'=>4,  'quantity_on_hand'=>45.0,'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>5,  'quantity_on_hand'=>8.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>6,  'quantity_on_hand'=>1.5, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()], // low
            ['ingridient_id'=>7,  'quantity_on_hand'=>3.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>8,  'quantity_on_hand'=>1.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()], // low
            ['ingridient_id'=>9,  'quantity_on_hand'=>5.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>10, 'quantity_on_hand'=>4.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>11, 'quantity_on_hand'=>3.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>12, 'quantity_on_hand'=>10.0,'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>13, 'quantity_on_hand'=>7.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>14, 'quantity_on_hand'=>120, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>15, 'quantity_on_hand'=>2.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>16, 'quantity_on_hand'=>6.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>17, 'quantity_on_hand'=>5.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
            ['ingridient_id'=>18, 'quantity_on_hand'=>8.0, 'last_updated'=>now(),'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Components (recipe — which ingredients each menu item uses)
        DB::table('components')->insert([
            // Nasi Goreng Special (1)
            ['menus_id'=>1,'ingridients_id'=>4, 'quantity'=>'0.3', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>1,'ingridients_id'=>1, 'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>1,'ingridients_id'=>2, 'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>1,'ingridients_id'=>6, 'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>1,'ingridients_id'=>12,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Mie Goreng Spesial (2)
            ['menus_id'=>2,'ingridients_id'=>5, 'quantity'=>'0.2', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>2,'ingridients_id'=>1, 'quantity'=>'0.08','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>2,'ingridients_id'=>2, 'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>2,'ingridients_id'=>12,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Ayam Bakar Madu (3)
            ['menus_id'=>3,'ingridients_id'=>1, 'quantity'=>'0.25','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>3,'ingridients_id'=>7, 'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>3,'ingridients_id'=>13,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            // Ikan Bakar Bumbu Bali (4) — Ikan Kakap stock is 0 => Sold Out demo
            ['menus_id'=>4,'ingridients_id'=>3, 'quantity'=>'0.3', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>4,'ingridients_id'=>7, 'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>4,'ingridients_id'=>12,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Soto Ayam (5)
            ['menus_id'=>5,'ingridients_id'=>1, 'quantity'=>'0.15','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>5,'ingridients_id'=>2, 'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>5,'ingridients_id'=>7, 'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Gado-Gado (6)
            ['menus_id'=>6,'ingridients_id'=>10,'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>6,'ingridients_id'=>11,'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>6,'ingridients_id'=>9, 'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            // Lumpia Goreng (7)
            ['menus_id'=>7,'ingridients_id'=>1, 'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>7,'ingridients_id'=>11,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>7,'ingridients_id'=>12,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Tahu Tempe Goreng (8)
            ['menus_id'=>8,'ingridients_id'=>12,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            // Salad Buah (9)
            ['menus_id'=>9,'ingridients_id'=>17,'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>9,'ingridients_id'=>16,'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>9,'ingridients_id'=>13,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Es Teh Manis (10)
            ['menus_id'=>10,'ingridients_id'=>14,'quantity'=>'1',   'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>10,'ingridients_id'=>13,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Jus Alpukat (11)
            ['menus_id'=>11,'ingridients_id'=>17,'quantity'=>'0.2', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>11,'ingridients_id'=>16,'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>11,'ingridients_id'=>13,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            // Es Jeruk (12) — no citrus ingredient; approximate with sugar
            ['menus_id'=>12,'ingridients_id'=>13,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            // Air Mineral (13) — no bottled-water ingredient; approximate with sugar token
            ['menus_id'=>13,'ingridients_id'=>13,'quantity'=>'0.001','created_at'=>now(),'updated_at'=>now()],
            // Kopi Hitam (14)
            ['menus_id'=>14,'ingridients_id'=>15,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Es Krim Vanilla (15)
            ['menus_id'=>15,'ingridients_id'=>16,'quantity'=>'0.05','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>15,'ingridients_id'=>13,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Pisang Goreng Keju (16)
            ['menus_id'=>16,'ingridients_id'=>12,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>16,'ingridients_id'=>16,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>16,'ingridients_id'=>13,'quantity'=>'0.02','created_at'=>now(),'updated_at'=>now()],
            // Kentang Goreng (17)
            ['menus_id'=>17,'ingridients_id'=>18,'quantity'=>'0.2', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>17,'ingridients_id'=>12,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
            // Onion Ring (18) — no onion-ring ingredient; approximate with shallot + oil
            ['menus_id'=>18,'ingridients_id'=>6, 'quantity'=>'0.1', 'created_at'=>now(),'updated_at'=>now()],
            ['menus_id'=>18,'ingridients_id'=>12,'quantity'=>'0.03','created_at'=>now(),'updated_at'=>now()],
        ]);

        // Sample orders (past 7 days)
        $orderData   = [];
        $detailData  = [];
        $orderId = 1;
        $sampleMenus = [
            ['menu_id'=>1,'price'=>35000],['menu_id'=>2,'price'=>32000],['menu_id'=>3,'price'=>45000],
            ['menu_id'=>10,'price'=>8000],['menu_id'=>11,'price'=>18000],['menu_id'=>15,'price'=>22000],
        ];

        for ($d = 6; $d >= 0; $d--) {
            $numOrders = rand(5, 14);
            for ($o = 0; $o < $numOrders; $o++) {
                $hour  = rand(10, 21);
                $min   = rand(0, 59);
                $date  = Carbon::today()->subDays($d)->setTime($hour, $min);
                $items = array_rand($sampleMenus, rand(1, 3));
                if (!is_array($items)) $items = [$items];

                $subtotal = 0;
                foreach ($items as $idx) {
                    $q = rand(1, 3);
                    $subtotal += $sampleMenus[$idx]['price'] * $q;
                }
                $total   = round($subtotal * 1.11);
                $types   = ['dine-in','dine-in','dine-in','takeaway','pre-order'];
                $payments= ['cash','cash','transfer','qris'];

                $orderData[] = [
                    'id'            => $orderId,
                    'order_date'    => $date,
                    'total_amount'  => $total,
                    'users_id'      => 2,
                    'users_roles_id'=> 2,
                    'customers_id'  => null,
                    'payment_types_id'=> 1,
                    'payment_type'  => $payments[array_rand($payments)],
                    'payment_date'  => $date,
                    'amount_paid'   => $total,
                    'table_id'      => rand(1, 7),
                    'order_type'    => $types[array_rand($types)],
                    'status'        => 'completed',
                    'customer_name' => 'Walk-in',
                    'notes'         => null,
                    'created_at'    => $date,
                    'updated_at'    => $date,
                ];

                foreach ($items as $idx) {
                    $q = rand(1, 3);
                    $detailData[] = [
                        'orders_id'  => $orderId,
                        'menus_id'   => $sampleMenus[$idx]['menu_id'],
                        'quantity'   => $q,
                        'price'      => $sampleMenus[$idx]['price'],
                        'subtotal'   => $sampleMenus[$idx]['price'] * $q,
                        'created_at' => $date,
                        'updated_at' => $date,
                    ];
                }
                $orderId++;
            }
        }

        // Today pending orders
        $orderData[] = [
            'id'=>$orderId,'order_date'=>now()->subMinutes(15),'total_amount'=>96470,
            'users_id'=>2,'users_roles_id'=>2,'customers_id'=>null,'payment_types_id'=>1,
            'payment_type'=>'cash','payment_date'=>null,'amount_paid'=>null,
            'table_id'=>4,'order_type'=>'dine-in','status'=>'pending',
            'customer_name'=>'Pak Budi','notes'=>null,'created_at'=>now(),'updated_at'=>now(),
        ];
        $detailData[] = ['orders_id'=>$orderId,'menus_id'=>1,'quantity'=>2,'price'=>35000,'subtotal'=>70000,'created_at'=>now(),'updated_at'=>now()];
        $detailData[] = ['orders_id'=>$orderId,'menus_id'=>10,'quantity'=>2,'price'=>8000,'subtotal'=>16000,'created_at'=>now(),'updated_at'=>now()];

        DB::table('orders')->insert($orderData);
        DB::table('order_details')->insert($detailData);

        // Reservations
        DB::table('reservations')->insert([
            ['customer_name'=>'Ibu Sari',   'phone'=>'0812-3456-7890','email'=>'sari@email.com',   'reservation_date'=>now()->toDateString(),              'reservation_time'=>'12:00:00','guests'=>4,'table_id'=>3,'status'=>'confirmed','source'=>'whatsapp','notes'=>'Meja dekat jendela','created_at'=>now(),'updated_at'=>now()],
            ['customer_name'=>'Pak Hendra', 'phone'=>'0856-7890-1234','email'=>null,               'reservation_date'=>now()->toDateString(),              'reservation_time'=>'19:00:00','guests'=>6,'table_id'=>6,'status'=>'confirmed','source'=>'online',   'notes'=>'Anniversary dinner','created_at'=>now(),'updated_at'=>now()],
            ['customer_name'=>'Mbak Dewi',  'phone'=>'0821-1122-3344','email'=>'dewi@email.com',   'reservation_date'=>now()->toDateString(),              'reservation_time'=>'13:30:00','guests'=>2,'table_id'=>1,'status'=>'pending', 'source'=>'online',   'notes'=>null,              'created_at'=>now(),'updated_at'=>now()],
            ['customer_name'=>'Keluarga Andi','phone'=>'0878-5566-7788','email'=>null,             'reservation_date'=>now()->addDay()->toDateString(),    'reservation_time'=>'18:00:00','guests'=>8,'table_id'=>8,'status'=>'pending', 'source'=>'offline',  'notes'=>'Ulang tahun anak','created_at'=>now(),'updated_at'=>now()],
            ['customer_name'=>'Bu Ratna',   'phone'=>'0896-4455-6677','email'=>'ratna@email.com',  'reservation_date'=>now()->addDays(2)->toDateString(),  'reservation_time'=>'12:00:00','guests'=>3,'table_id'=>5,'status'=>'pending', 'source'=>'online',   'notes'=>null,              'created_at'=>now(),'updated_at'=>now()],
        ]);

        // Purchase Orders
        DB::table('purchase_orders')->insert([
            ['id'=>1,'supplier_id'=>1,'order_date'=>now()->subDays(5),'expected_date'=>now()->subDays(2),'status'=>'received','total_amount'=>'875000','notes'=>'Restock protein bulanan','created_at'=>now(),'updated_at'=>now()],
            ['id'=>2,'supplier_id'=>2,'order_date'=>now()->subDays(2),'expected_date'=>now()->addDay(),  'status'=>'sent',    'total_amount'=>'320000','notes'=>'Sayuran minggu ini',     'created_at'=>now(),'updated_at'=>now()],
            ['id'=>3,'supplier_id'=>3,'order_date'=>now(),            'expected_date'=>now()->addDays(3),'status'=>'pending', 'total_amount'=>'450000','notes'=>'Bumbu dapur',            'created_at'=>now(),'updated_at'=>now()],
        ]);

        DB::table('purchase_order_items')->insert([
            ['purchase_order_id'=>1,'ingridient_id'=>1,'quantity'=>10,'unit_price'=>'35000','subtotal'=>'350000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>1,'ingridient_id'=>2,'quantity'=>15,'unit_price'=>'28000','subtotal'=>'420000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>1,'ingridient_id'=>3,'quantity'=>5, 'unit_price'=>'21000','subtotal'=>'105000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>2,'ingridient_id'=>10,'quantity'=>10,'unit_price'=>'8000','subtotal'=>'80000', 'created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>2,'ingridient_id'=>9, 'quantity'=>10,'unit_price'=>'10000','subtotal'=>'100000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>2,'ingridient_id'=>17,'quantity'=>7, 'unit_price'=>'20000','subtotal'=>'140000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>3,'ingridient_id'=>6, 'quantity'=>5, 'unit_price'=>'25000','subtotal'=>'125000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>3,'ingridient_id'=>7, 'quantity'=>5, 'unit_price'=>'30000','subtotal'=>'150000','created_at'=>now(),'updated_at'=>now()],
            ['purchase_order_id'=>3,'ingridient_id'=>8, 'quantity'=>4, 'unit_price'=>'43750','subtotal'=>'175000','created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}
