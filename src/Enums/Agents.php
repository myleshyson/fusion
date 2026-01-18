<?php

namespace Myleshyson\Fusion\Enums;

enum Agents: string
{
    case ClaudeCode = 'Claude Code';
    case OpenCode = 'OpenCode';
    case PhpStorm = 'PhpStorm';
    case Gemini = 'Gemini';
    case Copilot = 'Github Copilot';
    case Codex = 'OpenAI Codex';
    case Cursor = 'Cursor';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (Agents $agent) => $agent->value, self::cases());
    }

    public function skillLocation(string $rootPath): string
    {
        return match ($this) {
            Agents::ClaudeCode, Agents::OpenCode => "$rootPath/.claude/skills/",
            Agents::PhpStorm => "$rootPath/.junie/skills/",
            Agents::Copilot => "$rootPath/.copilot/skills/",
            Agents::Codex => "$rootPath/.codex/skills/",
            Agents::Gemini => "$rootPath/.gemini/skills/",
            Agents::Cursor => "$rootPath/.cursor/skills/",

        };
    }

    public function agentFileLocation(string $rootPath): string
    {
        return match ($this) {
            Agents::ClaudeCode => "$rootPath/.claude/CLAUDE.md",
            Agents::PhpStorm => "$rootPath/.junie/guidelines.md",
            Agents::Aider => "$rootPath/CONVENTIONS.md",
            default => "$rootPath/AGENTS.md"
        };
    }

    public function mcpLocation(string $rootPath): string
    {
        return match ($this) {
            Agents::ClaudeCode, Agents::OpenCode => "$rootPath/.claude/mcp/",
            Agents::PhpStorm => "$rootPath/.junie/mcp/",
            Agents::Copilot => "$rootPath/.copilot/mcp/",
            Agents::Codex => "$rootPath/.codex/mcp/",
            Agents::Gemini => "$rootPath/.gemini/mcp/",
            Agents::Cursor => "$rootPath/.cursor/mcp/",
        };
    }
}
