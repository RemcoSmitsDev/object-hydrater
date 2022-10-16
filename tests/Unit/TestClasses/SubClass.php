<?php

namespace RemcoSmits\Hydrate\Tests\Unit\TestClasses;

class SubClass
{
    private int $age = 89;

    public function getAge(): int
    {
        return $this->age;
    }
}