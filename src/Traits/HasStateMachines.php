<?php

namespace Workup\StateMachine\Traits;

use Illuminate\Support\Arr;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

trait HasStateMachines
{
    /** @var array $stateMachines */
//    public array $stateMachines = [];

    protected static FactoryInterface $smFactory;

    protected static function bootHasStateMachines()
    {
        static::$smFactory = app(FactoryInterface::class);
    }

    /** @noinspection PhpUnhandledExceptionInspection as requirements are always met */
    protected function initializeHasStateMachines()
    {
        collect($this->stateMachines)->keys()
            ->each(fn ($attribute) => static::$smFactory->addConfig(
                $this->getStateMachineConfig($attribute),
                $attribute)
            );
    }

    /**
     * @param  string  $attribute
     *
     * @return StateMachineInterface
     * @noinspection PhpUnhandledExceptionInspection as requirements are always met
     * @noinspection PhpDocMissingThrowsInspection as requirements are always met
     */
    public function getStateMachine(string $attribute = 'state'): StateMachineInterface
    {
        return static::$smFactory->get($this, $attribute);
    }

    /**
     * @param  string  $attribute
     *
     * @return array
     */
    public function getStateMachineConfig(string $attribute = 'state'): array
    {
        $config = Arr::get($this->stateMachines, $attribute);
        if (is_string($config)) {
            $config = config($config);
        }

        return $this->normalizeConfig($config, $attribute);
    }

    /**
     * @param  array  $config
     * @param $attribute
     *
     * @return array
     */
    protected function normalizeConfig(array $config, $attribute): array
    {
        return array_merge([
            'class' => get_class($this),
            'property_path' => $attribute,
        ], $config);
    }
}
