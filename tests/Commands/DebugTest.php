<?php

namespace Workup\StateMachine\Test\Commands;

use Illuminate\Contracts\Console\Kernel;
use Workup\StateMachine\Test\ConsoleHelpers;
use Workup\StateMachine\Test\TestCase;

class DebugTest extends TestCase
{
    use ConsoleHelpers;

    /**
     * @test
     */
    public function it_accepts_a_graph_argument()
    {
        // Act

        $this->artisan('winzou:state-machine:debug', ['graph' => 'graphA']);

        // Assert

        $this->seeInConsole('new')
            ->seeInConsole('ask_for_changes')
            ->withSuccessCode();
    }

    /**
     * @test
     */
    public function it_asks_for_a_graph()
    {
        // Arrange

        $config = $this->app['config']->get('state-machine', []);

        $command = \Mockery::spy('\Workup\StateMachine\Commands\Debug[choice]', [$config]);

        $choices = [
            'graphA'."\t(".'App\User - graphA)',
        ];

        $command->shouldReceive(['choice' => $choices[0]]);

        $this->app[Kernel::class]->registerCommand($command);

        // Act

        $this->artisan('winzou:state-machine:debug', ['--no-interaction' => true]);

        // Assert

        $command->shouldHaveReceived(
            'choice', ['Which state machine would you like to know about?', $choices, 0]
        )->once();

        $this->seeInConsole('pending_review')
            ->seeInConsole('cancel_changes')
            ->withSuccessCode();
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_configuration_is_empty()
    {
        // Arrange

        $command = \Mockery::spy('\Workup\StateMachine\Commands\Debug[error]', [[]]);

        $this->app[Kernel::class]->registerCommand($command);

        // Act

        $this->artisan('winzou:state-machine:debug', ['graph' => 'graphA']);

        // Assert

        $command->shouldHaveReceived(
            'error', ['There are no state machines configured.']
        )->once();

        $this->withoutSuccessCode();
    }

    /**
     * @test
     */
    public function it_returns_an_error_if_the_graph_is_not_found()
    {
        // Arrange

        $command = \Mockery::spy('\Workup\StateMachine\Commands\Debug[error]', [['foo' => []]]);

        $this->app[Kernel::class]->registerCommand($command);

        // Act

        $this->artisan('winzou:state-machine:debug', ['graph' => 'graphA']);

        // Assert

        $command->shouldHaveReceived(
            'error', ['The provided state machine graph is not configured.']
        )->once();

        $this->withoutSuccessCode();
    }
}
