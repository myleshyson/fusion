<?php

namespace Myleshyson\Fusion\Agents;

class Codex extends BaseAgent
{
    public static function optionName(): string
    {
        return 'codex';
    }

    public function name(): string
    {
        return 'OpenAI Codex';
    }

    /**
     * Codex can be detected via AGENTS.md or .codex/ directory.
     *
     * @return array<string>
     */
    public function detectionPaths(): array
    {
        return [
            'AGENTS.md',
            '.codex/',
        ];
    }

    public function guidelinesPath(): string
    {
        return 'AGENTS.md';
    }

    public function skillsPath(): string
    {
        return '.codex/skills/';
    }

    /**
     * Codex does not support project-local MCP config.
     * MCP is configured via ~/.codex/config.toml which is outside project scope.
     */
    public function mcpPath(): string
    {
        return '';
    }

    /**
     * Not used since mcpPath returns empty string.
     */
    protected function transformMcpConfig(array $servers): array
    {
        return [];
    }
}
