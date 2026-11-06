<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SetupSuperAdmin extends Command
{
    protected $signature = 'admin:setup {--email= : Email akun super administrator} {--name=Super Administrator : Nama akun}';

    protected $description = 'Buat akun super administrator satu kali dengan password acak.';

    public function handle(): int
    {
        $email = strtolower((string) ($this->option('email') ?: config('super-admin.email')));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email super administrator tidak valid.');

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Akun {$email} sudah ada. Perintah ini tidak mengubah password atau role akun yang ada.");

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => 'Administrator', 'description' => 'Akses penuh operasional & manajemen loyalty']
        );
        $password = Str::random(48);

        User::create([
            'name' => (string) $this->option('name'),
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $role->id,
            'email_verified_at' => now(),
        ]);

        $this->info('Akun Super Administrator berhasil dibuat. Simpan kredensial berikut sekarang:');
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");

        return self::SUCCESS;
    }
}