<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\PasswordSecurityService;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * IMPORTANTE: Usa PasswordSecurityService para gerar o hash com sal+pimenta,
     * pois o FortifyServiceProvider autentica via esse mesmo serviço.
     * Usar Hash::make() direto causaria falha de login permanente.
     */
    public function run(): void
    {
        $plainPassword = config('app.admin_password', 'teste');
        $adminEmail = config('app.admin_email', 'admin@admin.com');
        $adminName = config('app.admin_name', 'Administrator');

        // updateOrCreate garante que a senha seja sempre recalculada com salt+pepper
        // corretos, mesmo em seeds subsequentes sobre usuário já existente.
        $admin = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => $adminName,
                'password' => PasswordSecurityService::hashPassword($plainPassword),
                'email_verified_at' => now(),
                'lgpd_consent_at' => now(),
            ]
        );

        if (!$admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
