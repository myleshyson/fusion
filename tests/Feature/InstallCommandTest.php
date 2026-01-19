<?php

use Myleshyson\Mush\App;
use Myleshyson\Mush\Commands\InstallCommand;
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

    // Verify .mush directory structure
    expect("{$this->artifactPath}/.mush")->toBeDirectory();
    expect("{$this->artifactPath}/.mush/guidelines")->toBeDirectory();
    expect("{$this->artifactPath}/.mush/guidelines/.gitignore")->toBeFile();
    expect("{$this->artifactPath}/.mush/skills")->toBeDirectory();
    expect("{$this->artifactPath}/.mush/skills/.gitignore")->toBeFile();
    expect("{$this->artifactPath}/.mush/mcp.json")->toBeFile();

    // Verify agent files were created
    expect("{$this->artifactPath}/.claude/CLAUDE.md")->toBeFile();
    expect("{$this->artifactPath}/.claude/mcp.json")->toBeFile();
});

it('fails if fusion is already initialized', function () {
    // Create existing .mush directory
    mkdir("{$this->artifactPath}/.mush", 0777, true);

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
