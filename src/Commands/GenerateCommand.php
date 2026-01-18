<?php

namespace Myleshyson\Fusion\Commands;

use Myleshyson\Fusion\Enums\Agents;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Laravel\Prompts\multiselect;
use function Termwind\render;

#[AsCommand(
    name: 'generate',
    description: 'Does all the AI shit you dont want to do.',
)]
class GenerateCommand extends Command
{
    public function __invoke(
        InputInterface $input,
        OutputInterface $output,
        #[Option]
        ?bool $claude = null,
        #[Option]
        ?bool $copilot = null,
        #[Option]
        ?bool $opencode = null,
        #[Option]
        ?bool $junie = null,
        #[Option]
        ?bool $gemini = null,
        #[Option]
        ?bool $codex = null,
        #[Option]
        ?bool $cursor = null,
        #[Option]
        array $agentPaths = [],
        #[Option]
        array $skillPaths = [],
        #[Option]
        array $mcpPaths = []
    ): int {
        if (! $output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        render('<pre class="text-pink-500">
 ▄▄▄▄▄▄▄▄▄▄▄  ▄         ▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄▄▄▄▄▄▄▄▄▄  ▄▄        ▄
▐░░░░░░░░░░░▌▐░▌       ▐░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░▌      ▐░▌
▐░█▀▀▀▀▀▀▀▀▀ ▐░▌       ▐░▌▐░█▀▀▀▀▀▀▀▀▀  ▀▀▀▀█░█▀▀▀▀ ▐░█▀▀▀▀▀▀▀█░▌▐░▌░▌     ▐░▌
▐░▌          ▐░▌       ▐░▌▐░▌               ▐░▌     ▐░▌       ▐░▌▐░▌▐░▌    ▐░▌
▐░█▄▄▄▄▄▄▄▄▄ ▐░▌       ▐░▌▐░█▄▄▄▄▄▄▄▄▄      ▐░▌     ▐░▌       ▐░▌▐░▌ ▐░▌   ▐░▌
▐░░░░░░░░░░░▌▐░▌       ▐░▌▐░░░░░░░░░░░▌     ▐░▌     ▐░▌       ▐░▌▐░▌  ▐░▌  ▐░▌
▐░█▀▀▀▀▀▀▀▀▀ ▐░▌       ▐░▌ ▀▀▀▀▀▀▀▀▀█░▌     ▐░▌     ▐░▌       ▐░▌▐░▌   ▐░▌ ▐░▌
▐░▌          ▐░▌       ▐░▌          ▐░▌     ▐░▌     ▐░▌       ▐░▌▐░▌    ▐░▌▐░▌
▐░▌          ▐░█▄▄▄▄▄▄▄█░▌ ▄▄▄▄▄▄▄▄▄█░▌ ▄▄▄▄█░█▄▄▄▄ ▐░█▄▄▄▄▄▄▄█░▌▐░▌     ▐░▐░▌
▐░▌          ▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░░░░░░░░░░░▌▐░▌      ▐░░▌
 ▀            ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀▀▀▀▀▀▀▀▀▀▀  ▀        ▀▀
</pre>');

        render('<p class="text-white font-bold">Don\'t be a gonk. Let\'s organize these ai files!</p>');

        $agents = multiselect(
            label: 'Which agents would you like to support?',
            options: Agents::values(),
            default: [Agents::ClaudeCode->value, Agents::Copilot->value, Agents::OpenCode->value],
        );

        $workingDir = (string) ($input->getOption('working-dir') ?? getcwd());

        foreach ($agents as $agent) {
            $agentEnum = Agents::from($agent);
            $guidelinesPath = $agentEnum->agentFileLocation($workingDir);
            $skillLocation = $agentEnum->skillLocation($workingDir);
        }

        return Command::SUCCESS;
    }
}
