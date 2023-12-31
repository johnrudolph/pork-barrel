<?php

namespace App\Livewire\Synths;

use App\DTOs\LivewireDTO as DTO;
use Livewire\Mechanisms\HandleComponents\Synthesizers\Synth;

class DTOSynth extends Synth
{
    public static $key = 'dto';

    public static function match($target)
    {
        return $target instanceof DTO;
    }

    public function dehydrate(DTO $target)
    {
        $class = get_class($target);

        $dtoReflection = new \ReflectionClass($target);

        $properties = collect($dtoReflection->getProperties(\ReflectionProperty::IS_PUBLIC));

        $data = $properties->mapWithKeys(function (\ReflectionProperty $property) use ($target) {
            return [$property->name => data_get($target, $property->name)];
        });

        $meta = [
            'class' => $class,
        ];

        return [$data, $meta];
    }

    public function hydrate($data, $meta)
    {
        $class = $meta['class'];

        $dto = new $class(...$data);

        return $dto;
    }

    /**
     * Allow updates from the front end.
     */
    public function set(&$target, $key, $value)
    {
        $target->{$key} = $value;
    }
}
