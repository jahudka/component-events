<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;

use Nette\Application\Responses\VoidResponse;
use Nette\Application\UI\Presenter;


class ContainerPresenter extends Presenter {
    public function createComponentComponentWithChildren() : ComponentWithChildren {
        return new ComponentWithChildren();
    }

    public function actionDefault() : void {
        $this->sendResponse(new VoidResponse());
    }
}
