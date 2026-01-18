<?php

namespace Myleshyson\Fusion;

use Myleshyson\Fusion\Commands\GenerateCommand;
use Myleshyson\Fusion\Commands\InitCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

class App extends Application
{
    public static function build()
    {
        $app = new self('Fusion', '1.0.0');
        $app->addCommand(new GenerateCommand);
        $app->addCommand(new InitCommand);
        $app->setDefaultCommand('generate');

        return $app;
    }

    public function getDefinition(): InputDefinition
    {
        $definition = parent::getDefinition();

        $definition->addOption(new InputOption(
            name: 'working-directory',
            shortcut: 'w',
            mode: InputOption::VALUE_OPTIONAL,
            description: 'The working directory to run the command in.',
        ));

        return $definition;
    }
}
