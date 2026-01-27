<?php

use Myleshyson\Mush\App;
use Myleshyson\Mush\Commands\UpdateCommand;
use Zenstruck\Console\Test\TestCommand;

beforeEach(function () {
    $this->artifactPath = __DIR__.'/../artifacts/cleanup-test';
    cleanDirectory($this->artifactPath);
    mkdir($this->artifactPath, 0777, true);
});

afterEach(function () {
    cleanDirectory($this->artifactPath);
});

it('removes deleted skills from agent directories', function () {
    // Set up .mush directory with two skills
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/skills", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    // Create two skills
    mkdir("{$mushPath}/skills/tailwind", 0777, true);
    mkdir("{$mushPath}/skills/testing", 0777, true);
    file_put_contents("{$mushPath}/skills/tailwind/SKILL.md", '# Tailwind skill');
    file_put_contents("{$mushPath}/skills/testing/SKILL.md", '# Testing skill');

    // Create OpenCode config to trigger detection
    mkdir("{$this->artifactPath}/.opencode", 0777, true);
    mkdir("{$this->artifactPath}/.opencode/skills", 0777, true);

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // First update: both skills should be written
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.opencode/skills/tailwind/SKILL.md")->toBeFile();
    expect("{$this->artifactPath}/.opencode/skills/testing/SKILL.md")->toBeFile();

    // Remove one skill from .mush
    unlink("{$mushPath}/skills/testing/SKILL.md");
    rmdir("{$mushPath}/skills/testing");

    // Second update: testing skill should be removed from .opencode
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.opencode/skills/tailwind/SKILL.md")->toBeFile();
    expect("{$this->artifactPath}/.opencode/skills/testing")->not->toBeDirectory();
});

it('removes deleted commands from agent directories', function () {
    // Set up .mush directory with two commands
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/commands", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    // Create two commands
    file_put_contents("{$mushPath}/commands/deploy.md", '# Deploy command');
    file_put_contents("{$mushPath}/commands/test.md", '# Test command');

    // Create OpenCode config to trigger detection
    mkdir("{$this->artifactPath}/.opencode", 0777, true);
    mkdir("{$this->artifactPath}/.opencode/commands", 0777, true);

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // First update: both commands should be written
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.opencode/commands/deploy.md")->toBeFile();
    expect("{$this->artifactPath}/.opencode/commands/test.md")->toBeFile();

    // Remove one command from .mush
    unlink("{$mushPath}/commands/test.md");

    // Second update: test command should be removed from .opencode
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.opencode/commands/deploy.md")->toBeFile();
    expect("{$this->artifactPath}/.opencode/commands/test.md")->not->toBeFile();
});

it('removes deleted skills from multiple agent directories', function () {
    // Set up .mush directory with a skill
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/skills", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    mkdir("{$mushPath}/skills/legacy", 0777, true);
    file_put_contents("{$mushPath}/skills/legacy/SKILL.md", '# Legacy skill');

    // Create both Claude and OpenCode configs
    mkdir("{$this->artifactPath}/.claude", 0777, true);
    mkdir("{$this->artifactPath}/.claude/skills", 0777, true);
    file_put_contents("{$this->artifactPath}/.claude/CLAUDE.md", '# Placeholder');

    mkdir("{$this->artifactPath}/.opencode", 0777, true);
    mkdir("{$this->artifactPath}/.opencode/skills", 0777, true);

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // First update: skill should be written to both agents
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.claude/skills/legacy/SKILL.md")->toBeFile();
    expect("{$this->artifactPath}/.opencode/skills/legacy/SKILL.md")->toBeFile();

    // Remove the skill from .mush
    unlink("{$mushPath}/skills/legacy/SKILL.md");
    rmdir("{$mushPath}/skills/legacy");

    // Second update: skill should be removed from both agents
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.claude/skills/legacy")->not->toBeDirectory();
    expect("{$this->artifactPath}/.opencode/skills/legacy")->not->toBeDirectory();
});

it('removes Copilot prompt files with .prompt.md extension', function () {
    // Set up .mush directory with commands
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/commands", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    file_put_contents("{$mushPath}/commands/deploy.md", '# Deploy command');

    // Create Copilot config
    mkdir("{$this->artifactPath}/.github", 0777, true);
    mkdir("{$this->artifactPath}/.github/prompts", 0777, true);
    file_put_contents("{$this->artifactPath}/.github/copilot-instructions.md", '# Placeholder');

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // First update: command should be written as .prompt.md
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.github/prompts/deploy.prompt.md")->toBeFile();

    // Remove command from .mush
    unlink("{$mushPath}/commands/deploy.md");

    // Second update: .prompt.md file should be removed
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.github/prompts/deploy.prompt.md")->not->toBeFile();
});

it('removes Gemini TOML command files', function () {
    // Set up .mush directory with commands
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/commands", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    file_put_contents("{$mushPath}/commands/analyze.md", '# Analyze command');

    // Create Gemini config (GEMINI.md is at root, not in .gemini/)
    mkdir("{$this->artifactPath}/.gemini", 0777, true);
    mkdir("{$this->artifactPath}/.gemini/commands", 0777, true);
    file_put_contents("{$this->artifactPath}/GEMINI.md", '# Placeholder');

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // First update: command should be written as .toml
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.gemini/commands/analyze.toml")->toBeFile();

    // Remove command from .mush
    unlink("{$mushPath}/commands/analyze.md");

    // Second update: .toml file should be removed
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    expect("{$this->artifactPath}/.gemini/commands/analyze.toml")->not->toBeFile();
});

it('does not remove items when cleanup directory does not exist', function () {
    // Set up .mush directory
    $mushPath = "{$this->artifactPath}/.mush";
    mkdir($mushPath, 0777, true);
    mkdir("{$mushPath}/guidelines", 0777, true);
    mkdir("{$mushPath}/skills", 0777, true);
    file_put_contents("{$mushPath}/mcp.json", json_encode(['servers' => []]));

    mkdir("{$mushPath}/skills/new-skill", 0777, true);
    file_put_contents("{$mushPath}/skills/new-skill/SKILL.md", '# New skill');

    // Create OpenCode config but no skills directory yet
    mkdir("{$this->artifactPath}/.opencode", 0777, true);
    // Don't create .opencode/skills directory

    $command = new UpdateCommand;
    $command->setApplication(App::build());

    // This should not throw an error even though skills directory doesn't exist
    TestCommand::for($command)
        ->execute("--working-dir={$this->artifactPath}")
        ->assertSuccessful();

    // Skills directory should be created with the skill
    expect("{$this->artifactPath}/.opencode/skills/new-skill/SKILL.md")->toBeFile();
});
