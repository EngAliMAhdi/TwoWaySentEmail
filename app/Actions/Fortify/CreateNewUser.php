<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailNewUser;
use App\Jobs\SendEmailMessage;
use App\Jobs\SmsJob;

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
        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
        ]);
        // First way to send email using queue is to use the queue method on the Mail facade
        // Mail::to($user->email)->queue(new EmailNewUser($user));
        // Second way to send email using queue is to create a job and dispatch it
        SendEmailMessage::dispatch($user);
        SmsJob::dispatch($user)->delay(now()->addSeconds(40));
        $admin = User::first();
        // Send notification to admin when a new user registers using queue
        $admin->notify(new \App\Notifications\NewUserRegistered($user));
        return $user;
    }
}
