<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            'dashboard',

            // Updtae Order
            'update-order',

            // Other Setting
            'other-setting',

            // Other Setting
            'other-setting',

            // Kategori Bonus
            'kategori-bonus',

            // Pengeluaran
            'pengeluaran',

            // Laporan Sewa
            'laporan-sewa',

            // Laporan Barang
            'laporan-barang',

            // Laporan Pengembalian
            'laporan-pengembalian',

            // Laporan Metode Pembayaran
            'laporan-metode-pembayaran',

            // Bonus
            'bonus',

            // Attendece Superadmin
            // 'attendance-superadmin',
            'attendance-list',
            'attendance-create',
            'attendance-edit',
            'attendance-delete',

            // User Permission
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',

            // Role Permission
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',

            // Supplier Permission
            'supplier-list',
            'supplier-create',
            'supplier-edit',
            'supplier-delete',

            // Material Permission
            'material-list',
            'material-create',
            'material-edit',
            'material-delete',

            // Product Permission
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',

            // Tag Permission
            'tag-list',
            'tag-create',
            'tag-edit',
            'tag-delete',

            // Addon Permission
            'addon-list',
            'addon-create',
            'addon-edit',
            'addon-delete',

            // Coupon Permission
            'coupon-list',
            'coupon-create',
            'coupon-edit',
            'coupon-delete',

            // Other Setting Permission
            'other-setting',

            // Customer Permission
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',

            // Table Permission
            'table-list',
            'table-create',
            'table-edit',
            'table-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }
    }
}
