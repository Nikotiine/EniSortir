<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

class EventsFilterModel
{
    #[ORM\Column(type: 'string')]
    public $campus = '';

    #[ORM\Column(type: 'string')]
    public $searchBar = '';

    #[ORM\Column(type: 'datetime')]
    public $minDate;

    #[ORM\Column(type: 'datetime')]
    public $maxDate;

    #[ORM\Column(type: 'boolean')]
    public $isOrganizer = true;

    #[ORM\Column(type: 'boolean')]
    public $isRegistred = true;

    #[ORM\Column(type: 'boolean')]
    public $isPassed = false;
}
