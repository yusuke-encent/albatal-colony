<?php

namespace App\Console\Commands;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CreateAdminUserCommand extends Command
{
    use PasswordValidationRules, ProfileValidationRules;

    protected $signature = 'user:create-admin
                            {name? : The admin user\'s name}
                            {email? : The admin user\'s email address}
                            {--password= : The admin user\'s password}';

    protected $description = 'Create an administrator user.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $input = $this->inputValues();

        $validator = Validator::make(
            $input,
            $this->rules(),
            $this->messages(),
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->create([
            'name' => $input['name'],
            'email' => $input['email'],
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
            'password' => $input['password'],
        ]);

        $this->components->info("Admin user [{$user->email}] created successfully.");

        return self::SUCCESS;
    }

    /**
     * @return array{name: string, email: string, password: string, password_confirmation: string}
     */
    protected function inputValues(): array
    {
        $password = $this->option('password');

        if (! is_string($password) || $password === '') {
            if (! $this->input->isInteractive()) {
                $password = '';
                $passwordConfirmation = '';
            } else {
                $password = (string) $this->secret('Password');
                $passwordConfirmation = (string) $this->secret('Confirm password');
            }
        } else {
            $passwordConfirmation = $password;
        }

        return [
            'name' => $this->resolveName(),
            'email' => $this->resolveEmail(),
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ];
    }

    protected function resolveName(): string
    {
        $name = $this->argument('name');

        if (is_string($name) && $name !== '') {
            return $name;
        }

        if (! $this->input->isInteractive()) {
            return '';
        }

        return (string) $this->ask('Name');
    }

    protected function resolveEmail(): string
    {
        $email = $this->argument('email');

        if (is_string($email) && $email !== '') {
            return $email;
        }

        if (! $this->input->isInteractive()) {
            return '';
        }

        return (string) $this->ask('Email address');
    }

    /**
     * @return array<string, array<int, Rule|array<mixed>|string>>
     */
    protected function rules(): array
    {
        return [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Please enter a name.',
            'email.required' => 'Please enter an email address.',
            'email.unique' => 'That email address is already in use.',
            'password.required' => 'Please enter an initial password.',
            'password.confirmed' => 'Password confirmation does not match.',
        ];
    }
}
