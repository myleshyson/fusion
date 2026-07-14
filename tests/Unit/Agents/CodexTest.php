<?php

use Myleshyson\Mush\Agents\Codex\Codex;

beforeEach(function () {
    $this->artifactPath = __DIR__.'/../../artifacts/agents/codex';
    cleanDirectory($this->artifactPath);
    mkdir($this->artifactPath, 0777, true);
});

afterEach(function () {
    cleanDirectory($this->artifactPath);
});

describe('Codex', function () {
    it('returns correct name', function () {
        $agent = new Codex($this->artifactPath);
        expect($agent->name())->toBe('OpenAI Codex');
    });

    it('returns correct paths', function () {
        $agent = new Codex($this->artifactPath);
        expect($agent->guidelines()->path())->toBe('AGENTS.md');
        expect($agent->skills()->path())->toBe('.agents/skills/');
        // Codex does not support MCP
        expect($agent->mcp())->toBeNull();
    });

    it('returns correct detection paths', function () {
        $agent = new Codex($this->artifactPath);
        // AGENTS.md is shared with OpenCode, so use Codex-specific paths.
        expect($agent->detectionPaths())->toBe([
            '.agents/skills/',
            '.codex/',
        ]);
    });

    it('detects when .agents skills directory exists', function () {
        mkdir("{$this->artifactPath}/.agents/skills", 0777, true);
        $agent = new Codex($this->artifactPath);
        expect($agent->detect())->toBeTrue();
    });

    it('detects when .codex directory exists', function () {
        mkdir("{$this->artifactPath}/.codex", 0777, true);
        $agent = new Codex($this->artifactPath);
        expect($agent->detect())->toBeTrue();
    });

    it('does not detect when only AGENTS.md exists', function () {
        // AGENTS.md alone should not trigger Codex detection (OpenCode uses it too)
        file_put_contents("{$this->artifactPath}/AGENTS.md", '# Test');
        $agent = new Codex($this->artifactPath);
        expect($agent->detect())->toBeFalse();
    });

    it('does not support MCP', function () {
        $agent = new Codex($this->artifactPath);
        expect($agent->mcp())->toBeNull();

        // No file should be created since MCP is not supported
        expect(file_exists("{$this->artifactPath}/.agents/mcp.json"))->toBeFalse();
        expect(file_exists("{$this->artifactPath}/.codex/mcp.json"))->toBeFalse();
    });

    it('writes skills to the .agents directory', function () {
        $agent = new Codex($this->artifactPath);
        $agent->skills()->write([
            'testing' => [
                'name' => 'testing',
                'description' => 'Runs tests',
                'content' => '# Testing',
            ],
        ]);

        expect("{$this->artifactPath}/.agents/skills/testing/SKILL.md")->toBeFile();
        expect(file_get_contents("{$this->artifactPath}/.agents/skills/testing/SKILL.md"))
            ->toContain('# Testing');
        expect("{$this->artifactPath}/.codex/skills/testing/SKILL.md")->not->toBeFile();
    });

    it('does not support agents', function () {
        $agent = new Codex($this->artifactPath);
        expect($agent->agents())->toBeNull();
    });

    it('does not support commands', function () {
        $agent = new Codex($this->artifactPath);
        expect($agent->commands())->toBeNull();
    });
});
