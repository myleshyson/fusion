<?php

use Myleshyson\Fusion\App;
use Myleshyson\Fusion\Commands\InstallCommand;
use Zenstruck\Console\Test\TestCommand;

beforeEach(function () {
    $this->artifactPath = __DIR__.'/../artifacts/install-command';
    cleanDirectory($this->artifactPath);
    mkdir($this->artifactPath, 0777, true);
});

afterEach(function () {
    cleanDirectory($this->artifactPath);
});

it('initializes a fusion project correctly', function () {
    $command = new InstallCommand;
    $command->setApplication(App::build());

    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath} --claude")
        ->assertSuccessful()
        ->assertOutputContains('Fusion initialized successfully!');

    // Verify .fusion directory structure
    expect("{$this->artifactPath}/.fusion")->toBeDirectory();
    expect("{$this->artifactPath}/.fusion/guidelines")->toBeDirectory();
    expect("{$this->artifactPath}/.fusion/guidelines/.gitignore")->toBeFile();
    expect("{$this->artifactPath}/.fusion/skills")->toBeDirectory();
    expect("{$this->artifactPath}/.fusion/skills/.gitignore")->toBeFile();
    expect("{$this->artifactPath}/.fusion/mcp.json")->toBeFile();

    // Verify agent files were created
    expect("{$this->artifactPath}/.claude/CLAUDE.md")->toBeFile();
    expect("{$this->artifactPath}/.claude/mcp.json")->toBeFile();
});

it('fails if fusion is already initialized', function () {
    // Create existing .fusion directory
    mkdir("{$this->artifactPath}/.fusion", 0777, true);

    $command = new InstallCommand;
    $command->setApplication(App::build());

    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath} --claude")
        ->assertStatusCode(1)
        ->assertOutputContains('already initialized');
});

it('supports multiple agents via options', function () {
    $command = new InstallCommand;
    $command->setApplication(App::build());

    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath} --claude --cursor")
        ->assertSuccessful();

    // Verify both agent files were created
    expect("{$this->artifactPath}/.claude/CLAUDE.md")->toBeFile();
    expect("{$this->artifactPath}/.cursorrules")->toBeFile();
});

it('supports all available agent options', function () {
    $command = new InstallCommand;
    $command->setApplication(App::build());

    // Test with all agents
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath} --claude --opencode --cursor --copilot --gemini --codex --junie")
        ->assertSuccessful();

    // Verify all agent files were created
    expect("{$this->artifactPath}/.claude/CLAUDE.md")->toBeFile();
    expect("{$this->artifactPath}/AGENTS.md")->toBeFile(); // Used by both OpenCode and Codex
    expect("{$this->artifactPath}/.cursorrules")->toBeFile();
    expect("{$this->artifactPath}/.github/copilot-instructions.md")->toBeFile();
    expect("{$this->artifactPath}/GEMINI.md")->toBeFile();
    expect("{$this->artifactPath}/.junie/guidelines.md")->toBeFile();
});
