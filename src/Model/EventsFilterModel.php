<?php

namespace App\Model;

use Doctrine\ORM\Mapping as ORM;

class EventsFilterModel
{
    #[ORM\Column(type: 'string')]
    public ?string $campus = '';

    #[ORM\Column(type: 'string')]
    public ?string $searchBar = '';

    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $minDate;

    #[ORM\Column(type: 'datetime')]
    public ?\DateTime $maxDate;

    #[ORM\Column(type: 'boolean')]
    public ?bool $isOrganizer = true;

    #[ORM\Column(type: 'boolean')]
    public ?bool $isRegistred = true;

    #[ORM\Column(type: 'boolean')]
    public ?bool $isPassed = false;
}
