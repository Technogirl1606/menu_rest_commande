<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';

    protected $description = "Crée un compte administrateur. Seul moyen de créer un compte, l'inscription publique étant désactivée.";

    public function handle(): int
    {
        $name = $this->ask('Nom complet');
        $email = $this->ask('Adresse email');
        $password = $this->secret('Mot de passe (8 caractères minimum)');

        $validator = Validator::make(
            compact('name', 'email', 'password'),
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:8'],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            // Un compte créé en ligne de commande n'a personne à qui envoyer
            // un email de vérification — on le marque directement comme vérifié,
            // sinon la route /admin/* (protégée par le middleware "verified")
            // resterait bloquée après la connexion.
            'email_verified_at' => now(),
        ]);

        $this->info("Compte administrateur créé pour {$email}.");

        return self::SUCCESS;
    }
}