<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\Application\IPresenter;


interface IRelay {

    public function setPresenter(IPresenter $presenter) : void;

}
