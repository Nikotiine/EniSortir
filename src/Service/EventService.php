<?php

namespace App\Service;

use App\Repository\CampusRepository;
use App\Repository\EventRepository;
use App\Repository\UserRepository;

class EventService
{
    public function __construct(private UserRepository $userRepository,
                                private CampusRepository $campusRepository,
                                private EventRepository $repository)
    {
    }
}
