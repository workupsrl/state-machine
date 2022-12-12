<?php

namespace Workup\StateMachine\Traits;

use Illuminate\Support\Arr;
use SM\Factory\FactoryInterface;
use SM\StateMachine\StateMachineInterface;

trait HasStateMachines
{
    protected static FactoryInterface $smFactory;

    /** @noinspection PhpUnhandledExceptionInspection as requirements are always met */
    protected static function bootHasStateMachines()
    {
        static::$smFactory = app(FactoryInterface::class);
        collect(static::$stateMachines)
            ->keys()
            ->each(fn($attribute) => static::$smFactory->addConfig(static::getStateMachineConfig($attribute), $attribute));
    }

    protected function initializeHasStateMachines()
    {
        collect(static::$stateMachines)
            ->keys()
            ->each(fn($attribute) => $this->setInitialState($attribute));
    }

    /**
     * @param string $attribute
     * @return StateMachineInterface
     * @noinspection PhpUnhandledExceptionInspection as requirements are always met
     * @noinspection PhpDocMissingThrowsInspection as requirements are always met
     */
    public function getStateMachine(string $attribute = 'state'): StateMachineInterface
    {
        return static::$smFactory->get($this, $attribute);
    }

    /**
     * @param string $attribute
     * @return array
     */
    public static function getStateMachineConfig(string $attribute = 'state'): array
    {
        $config = Arr::get(static::$stateMachines, $attribute);
        if (is_string($config)) {
            $config = config($config);
        }

        return static::normalizeConfig($config, $attribute);
    }

    /**
     * @param array $config
     * @param $attribute
     * @return array
     */
    protected static function normalizeConfig(array $config, $attribute): array
    {
        return array_merge([
            'class' => get_called_class(),
            'property_path' => $attribute,
        ], $config);
    }

    protected function setInitialState(string $attribute)
    {
        if (!$state = Arr::get(static::$stateMachines, "$attribute.initial_state")) {
            $state = Arr::get(static::getStateMachineConfig($attribute), 'states.0');
        }

        $this->setAttribute($attribute, $state);
    }
}
