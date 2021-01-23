<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents;

use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\Statement;


interface IBridge {

    /**
     * This method should detect the presence of the event dispatcher
     * implementation an integration is bridging to. If it returns false
     * no further processing for the implementation is performed.
     */
    public function detectPresence(ContainerBuilder $builder) : bool;

    /**
     * This method should create an instance of the integration's
     * IAnalyser implementation.
     */
    public function createAnalyser() : IAnalyser;

    /**
     * This method should convert a listener map obtained by static
     * analysis of the component tree into a DI Statement which creates
     * an instance of the IRelay implementation of the integration.
     *
     * The listener map has the following structure:
     *
     * $listeners = [
     *   <presenter class> => [
     *     <component path> => <events>
     *   ]
     * ]
     *
     * The value of <events> is the array returned from IAnalyser::analyse().
     * The <component path> will be empty for events of the presenter itself.
     * In practice it is a good idea to reshape the listener map into a structure
     * that is easily queried for all events of a given presenter and all its
     * descendant components so that the relay doesn't need to traverse a deep
     * structure at runtime in order to subscribe events for the presenter.
     */
    public function createRelayFactory(array $listeners) : Statement;

}
