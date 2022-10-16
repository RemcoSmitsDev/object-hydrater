<?php

declare(strict_types=1);

namespace RemcoSmits\Hydrate\Exception;

use RemcoSmits\Hydrate\Reflection\HydrateReflectionProperty;

final class ValueWasNotFoundException extends AbstractHydrateException
{
    private HydrateReflectionProperty $property;

    public function __construct(HydrateReflectionProperty $property)
    {
        $this->property = $property;

        parent::__construct(
            sprintf(
                "There key [%s] was not found inside the given data!",
                $property->getName()
            )
        );
    }

    public function getProperty(): HydrateReflectionProperty
    {
        return $this->property;
    }
}