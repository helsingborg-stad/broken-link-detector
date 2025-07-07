<?php

namespace BrokenLinkDetector\Cli;

use PHPUnit\Framework\TestCase;
use WpService\Implementations\FakeWpService;
use BrokenLinkDetector\Config\Config;
use BrokenLinkDetector\Cli\CommandRunner;
use BrokenLinkDetector\Cli\CommandInterface;
use AcfService\Implementations\FakeAcfService;

class CommandRunnerTest extends TestCase
{
    private FakeWpService $wpService;
    private Config $config;
    private CommandRunner $commandRunner;

    protected function setUp(): void
    {
        $this->wpService = new FakeWpService([
            'applyFilters' => function($filter, $value) {
                return $value;
            },
            '__' => function($text, $domain) {
                return $text;
            },
            'wpautop' => function($text) {
                return $text;
            }
        ]);

        $acfService = new FakeAcfService([
            'getField' => []
        ]);

        $this->config = new Config(
            $this->wpService,
            $acfService,
            'BrokenLinkDetector/Config',
            '/path/to/plugin',
            'https://example.com/plugin'
        );

        $this->commandRunner = new CommandRunner($this->wpService, $this->config);
    }

    /**
     * @testdox addCommand adds command to runner
     */
    public function testAddCommand(): void
    {
        $command = $this->createMockCommand('test-command');
        $result = $this->commandRunner->addCommand($command);
        
        $this->assertInstanceOf(CommandRunner::class, $result);
        $this->assertArrayHasKey('test-command', $this->commandRunner->getCommands());
    }

    /**
     * @testdox getCommands returns all registered commands
     */
    public function testGetCommands(): void
    {
        $command1 = $this->createMockCommand('command1');
        $command2 = $this->createMockCommand('command2');
        
        $this->commandRunner->addCommand($command1);
        $this->commandRunner->addCommand($command2);
        
        $commands = $this->commandRunner->getCommands();
        $this->assertCount(2, $commands);
        $this->assertArrayHasKey('command1', $commands);
        $this->assertArrayHasKey('command2', $commands);
    }

    /**
     * @testdox runCommand throws exception for non-existent command
     */
    public function testRunCommandThrowsExceptionForNonExistentCommand(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Command not found');
        
        $this->commandRunner->runCommand('non-existent', [], []);
    }

    /**
     * @testdox runCommand executes command handler
     */
    public function testRunCommandExecutesHandler(): void
    {
        $handlerCalled = false;
        $passedArgs = null;
        $passedOptions = null;

        $command = $this->createMockCommand('test-command', function($args, $options) use (&$handlerCalled, &$passedArgs, &$passedOptions) {
            $handlerCalled = true;
            $passedArgs = $args;
            $passedOptions = $options;
        });

        $this->commandRunner->addCommand($command);
        
        $testArgs = ['arg1', 'arg2'];
        $testOptions = ['option1' => 'value1'];
        
        $this->commandRunner->runCommand('test-command', $testArgs, $testOptions);
        
        $this->assertTrue($handlerCalled);
        $this->assertEquals($testArgs, $passedArgs);
        $this->assertEquals($testOptions, $passedOptions);
    }

    /**
     * @testdox registerWithWPCLI returns false when WP_CLI not available
     */
    public function testRegisterWithWPCLIReturnsFalseWhenNotAvailable(): void
    {
        $result = $this->commandRunner->registerWithWPCLI();
        $this->assertFalse($result);
    }

    /**
     * @testdox registerWithWPCLI returns true when WP_CLI available
     */
    public function testRegisterWithWPCLIReturnsTrueWhenAvailable(): void
    {
        // Mock WP_CLI being available
        if (!defined('WP_CLI')) {
            define('WP_CLI', true);
        }
        
        $result = $this->commandRunner->registerWithWPCLI();
        $this->assertTrue($result);
    }

    /**
     * Create a mock command for testing
     */
    private function createMockCommand(string $name, callable $handler = null): CommandInterface
    {
        return new class($name, $handler) implements CommandInterface {
            private string $name;
            private $handler;

            public function __construct(string $name, callable $handler = null)
            {
                $this->name = $name;
                $this->handler = $handler ?: function($args, $options) {};
            }

            public function getCommandName(): string
            {
                return $this->name;
            }

            public function getCommandDescription(): string
            {
                return 'Test command description';
            }

            public function getCommandHelp(): string
            {
                return 'Test command help';
            }

            public function getCommandArguments(): array
            {
                return [
                    'arg1' => 'First argument',
                    'arg2' => 'Second argument'
                ];
            }

            public function getCommandOptions(): array
            {
                return [
                    'option1' => 'First option',
                    'option2' => 'Second option'
                ];
            }

            public function getCommandHandler(): callable
            {
                return $this->handler;
            }
        };
    }
}