<?php

namespace Myleshyson\Fusion\Agents;

class OpenCode
{
    public function __construct(
        protected string $cwd
    ) {}

    public function guidelinesPath(): string
    {
        return 'AGENTS.md';
    }

    public function skillsPath(): string
    {
        return '.opencode/skills/';
    }

    public function mcpPath(): string
    {
        return '.opencode/opencode.json';
    }

    public function updateMcpConfig(array $mcpConfig): void
    {
        $opencodeConfig = file_get_contents($this->cwd.'/'.$this->mcpPath());
        if (isset($opencodeConfig['mcp'])) {
            $opencodeConfig['mcp'] = array_merge($opencodeConfig['mcp'], $mcpConfig);
            file_put_contents($this->cwd.'/'.$this->mcpPath(), json_encode($opencodeConfig, JSON_PRETTY_PRINT));
        }
    }
}
