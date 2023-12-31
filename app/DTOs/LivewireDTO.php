<?php

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;

abstract class LivewireDTO implements Arrayable
{
    public function toArray(): array
    {
        $dtoReflection = new \ReflectionClass($this::class);

        $properties = collect($dtoReflection->getProperties(\ReflectionProperty::IS_PUBLIC));

        return $properties->mapWithKeys(
            fn (\ReflectionProperty $property) => [$property->name => data_get($this, $property->name)]
        )->toArray();
    }
}
