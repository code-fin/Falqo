<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Company::with('users')->each(function (Company $company): void {
            $owner = Role::firstOrCreate(['company_id' => $company->id, 'name' => 'owner', 'guard_name' => 'web']);
            $employee = Role::firstOrCreate(['company_id' => $company->id, 'name' => 'employee', 'guard_name' => 'web']);
            setPermissionsTeamId($company->id);

            foreach ($company->users as $index => $user) {
                if ($user->roles()->where('roles.company_id', $company->id)->doesntExist()) {
                    $user->assignRole($index === 0 ? $owner : $employee);
                }
            }
        });
    }
}
