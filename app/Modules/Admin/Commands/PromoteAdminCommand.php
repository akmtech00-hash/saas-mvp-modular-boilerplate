<?php

declare(strict_types=1);

namespace App\Modules\Admin\Commands;

use App\Modules\Auth\Models\User;
use Illuminate\Console\Command;

class PromoteAdminCommand extends Command
{
    protected $signature = 'saas:promote-admin {email : The email address of the user to promote} {--demote : Demote user from Super Admin}';
    protected $description = 'Promote or demote a user to/from SaaS Super Administrator status';

    public function handle(): int
    {
        $email = $this->argument('email');
        $demote = $this->option('demote');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return Command::FAILURE;
        }

        $user->update([
            'is_super_admin' => !$demote,
        ]);

        $action = $demote ? 'demoted from' : 'promoted to';
        $this->info("User '{$user->name}' ({$email}) successfully {$action} Super Administrator!");

        return Command::SUCCESS;
    }
}
