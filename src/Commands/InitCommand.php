<?php

namespace Myleshyson\Fusion\Commands;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'init',
    description: 'Initializes a new Fusion project in the specified directory.',
)]
class InitCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option(
            description: 'The folder where the Fusion project will be initialized.',
            name: 'folder',
            shortcut: 'f',
        )]
        string $folder = '.fusion',
    ): int {

        if (! $output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "OutputInterface".');
        }

        /** @var string $cwd */
        $cwd = $input->getOption('working-directory') ?? getcwd();
        $folderPath = $cwd.DIRECTORY_SEPARATOR.$folder;

        if (! is_dir($folderPath)) {
            mkdir($folderPath.DIRECTORY_SEPARATOR.'guidelines', 0777, true);
            file_put_contents($folderPath.DIRECTORY_SEPARATOR.'guidelines'.DIRECTORY_SEPARATOR.'.gitignore', "*\n!.gitignore\n");

            mkdir($folderPath.DIRECTORY_SEPARATOR.'skills', 0777, true);
            file_put_contents($folderPath.DIRECTORY_SEPARATOR.'skills'.DIRECTORY_SEPARATOR.'.gitignore', "*\n!.gitignore\n");

            mkdir($folderPath.DIRECTORY_SEPARATOR.'mcp', 0777, true);
            file_put_contents($folderPath.DIRECTORY_SEPARATOR.'mcp'.DIRECTORY_SEPARATOR.'.gitignore', "*\n!.gitignore\n");

            file_put_contents($folderPath.DIRECTORY_SEPARATOR.'fusion.yaml', "project_name: My Fusion Project\n");

            $output->writeln("Project initialized successfully in {$folderPath}.");
        } else {
            $output->writeln("The folder {$folderPath} already exists. Initialization skipped.");
        }

        return Command::SUCCESS;
    }
}
