<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AccountRecoveryMail extends Mailable
{
    use SerializesModels;

    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    // Dans AccountRecoveryMail.php

public function build()
{
    $token = $this->token;  // Le token de récupération passé dans le constructeur

    return $this->subject('Récupération de votre compte')
                ->view('emails.account_recovery')
                ->with(['token' => $token]);  // Envoie du token dans la vue
}


}
