<?php

declare(strict_types=1);

namespace Jahudka\ComponentEvents\Tests\Mocks;


class ReturnTypes80 {
    public function unionOfClasses() : ComponentWithChildren | ChildComponent {
        return new ChildComponent();
    }

    public function unionOfClassAndNull() : ComponentWithChildren | null {
        return new ComponentWithChildren();
    }
}
