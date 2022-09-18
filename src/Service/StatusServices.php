<?php

namespace App\Service;

use App\Command\AutoStatusCommands;

class StatusServices
{

    public function __construct(private AutoStatusCommands $commands)
    {

    }
    public function verifyActiveStatus(): void
    {
   // $this->commands->autoUpdatedStatus();
    $this->commands->verifyCheckRegistration();
    }

}