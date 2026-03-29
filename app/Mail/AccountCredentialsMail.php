<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountCredentialsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    public $name;
    public $roleName;

    /**
     * Create a new message instance.
     *
     * @param string $email
     * @param string $password
     * @param string $name
     * @param string $roleName
     */
    public function __construct($email, $password, $name, $roleName = 'User')
    {
        $this->email = $email;
        $this->password = $password;
        $this->name = $name;
        $this->roleName = $roleName;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'OSA Hub - Your Account Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.account-credentials',
            with: [
                'email' => $this->email,
                'password' => $this->password,
                'name' => $this->name,
                'roleName' => $this->roleName,
            ],
        );
    }

    /**
     * Build the message (fallback for older Laravel versions).
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('OSA Hub - Your Account Credentials')
            ->view('emails.account-credentials')
            ->with([
                'email' => $this->email,
                'password' => $this->password,
                'name' => $this->name,
                'roleName' => $this->roleName,
            ]);
    }
}
