<?php

use Myleshyson\Fusion\App;
use Myleshyson\Fusion\Commands\InitCommand;
use Zenstruck\Console\Test\TestCommand;

beforeEach(function () {
    $this->artifactPath = __DIR__.'/../artifacts/init-command';

    cleanDirectory($this->artifactPath);

    mkdir($this->artifactPath, 0777, true);
});

afterEach(function () {
    cleanDirectory($this->artifactPath);
});

it('initializes a project correctly', function () {
    $command = new InitCommand;
    $command->setApplication(App::build());

    TestCommand::for($command)
        ->execute("-w $this->artifactPath")
        ->assertSuccessful()
        ->assertOutputContains('Project initialized successfully');

    expect("$this->artifactPath/.fusion")->toBeDirectory()
        ->and("$this->artifactPath/.fusion/fusion.yaml")->toBeFile()
        ->and("$this->artifactPath/.fusion/guidelines")->toBeDirectory()
        ->and("$this->artifactPath/.fusion/guidelines/.gitignore")->toBeFile()
        ->and("$this->artifactPath/.fusion/skills")->toBeDirectory()
        ->and("$this->artifactPath/.fusion/skills/.gitignore")->toBeFile()
        ->and("$this->artifactPath/.fusion/mcp")->toBeDirectory()
        ->and("$this->artifactPath/.fusion/mcp/.gitignore")->toBeFile();
});
