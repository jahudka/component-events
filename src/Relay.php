<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\Application\Application;
use Nette\Application\IPresenter;


class Relay {

    /** @var IRelay[] */
    private $relays = [];

    public function add(IRelay $relay) : void {
        $this->relays[] = $relay;
    }

    public function setPresenter(Application $application, IPresenter $presenter) : void {
        foreach ($this->relays as $relay) {
            $relay->setPresenter($presenter);
        }
    }

}
