<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\Application\IPresenter;


interface IRelay {

    /**
     * This method is be called from the `onPresenter` event
     * of the Nette\Application\Application class. It should
     * unsubscribe any events that have been subscribed for
     * a previous presenter, if any, and then subscribe all
     * relevant events for the current presenter and its
     * component tree.
     */
    public function setPresenter(IPresenter $presenter) : void;

}
