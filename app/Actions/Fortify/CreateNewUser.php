<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input): User {
            $company = Company::create(['name' => $input['name'].' Workspace']);
            $user = User::create(['company_id' => $company->id, 'name' => $input['name'], 'email' => $input['email'], 'password' => $input['password']]);
            setPermissionsTeamId($company->id);
            $user->assignRole(Role::firstOrCreate(['company_id' => $company->id, 'name' => 'owner', 'guard_name' => 'web']));

            return $user;
        });
    }
}
