<?php

use Laravel\Prompts\Key;
use Laravel\Prompts\Prompt;
use Myleshyson\Fusion\App;
use Myleshyson\Fusion\Commands\GenerateCommand;
use Zenstruck\Console\Test\TestCommand;

it('generates AGENTS.md file', function () {
    $command = new GenerateCommand;
    $command->setApplication(App::build());
    Prompt::fake([Key::ENTER]);
    TestCommand::for($command)
        ->execute()
        ->assertSuccessful();
});
