<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email : Email address of an existing verified account}';

    protected $description = 'Grant administrator access to an existing verified account';

    public function handle(): int
    {
        $user = User::where('email', strtolower($this->argument('email')))->first();

        if (! $user) {
            $this->error('No account was found for that email address.');

            return self::FAILURE;
        }

        if (! $user->hasVerifiedEmail()) {
            $this->error('Verify the account email before granting administrator access.');

            return self::FAILURE;
        }

        if ($user->role->isSuperAdmin()) {
            $this->info('This account already has super administrator access.');

            return self::SUCCESS;
        }

        if (! $this->confirm("Grant super administrator access to {$user->email}?")) {
            $this->warn('No changes were made.');

            return self::SUCCESS;
        }

        $user->role = UserRole::SuperAdmin;
        $user->save();

        $this->info("Administrator access granted to {$user->email}.");

        return self::SUCCESS;
    }
}
