<?php

namespace Myleshyson\Mush\Agents;

class ClaudeCode extends BaseAgent
{
    public static function optionName(): string
    {
        return 'claude';
    }

    public function name(): string
    {
        return 'Claude Code';
    }

    /**
     * Claude Code can be detected via multiple paths.
     *
     * @return array<string>
     */
    public function detectionPaths(): array
    {
        return [
            'CLAUDE.md',
            '.claude/CLAUDE.md',
            '.claude/',
            '.claude/mcp.json',
        ];
    }

    public function guidelinesPath(): string
    {
        return '.claude/CLAUDE.md';
    }

    public function skillsPath(): string
    {
        return '.claude/skills/';
    }

    public function mcpPath(): string
    {
        return '.claude/mcp.json';
    }

    protected function transformMcpConfig(array $servers): array
    {
        $mcpServers = [];

        foreach ($servers as $name => $config) {
            if (! is_array($config)) {
                continue;
            }

            $server = [];

            if (isset($config['command'])) {
                // Local server - split command array into command + args
                $command = $config['command'];
                $server['command'] = is_array($command) ? $command[0] : $command;
                if (is_array($command) && count($command) > 1) {
                    $server['args'] = array_slice($command, 1);
                }
                if (isset($config['env'])) {
                    $server['env'] = $config['env'];
                }
            } elseif (isset($config['url'])) {
                // Remote server
                $server['url'] = $config['url'];
                if (isset($config['headers'])) {
                    $server['headers'] = $config['headers'];
                }
            }

            $mcpServers[$name] = $server;
        }

        return ['mcpServers' => $mcpServers];
    }

    protected function mergeMcpConfig(array $existing, array $new): array
    {
        // Merge mcpServers specifically
        $existingServers = isset($existing['mcpServers']) && is_array($existing['mcpServers']) ? $existing['mcpServers'] : [];
        $newServers = isset($new['mcpServers']) && is_array($new['mcpServers']) ? $new['mcpServers'] : [];

        if (! empty($existingServers) && ! empty($newServers)) {
            $new['mcpServers'] = array_replace_recursive($existingServers, $newServers);
        }

        return array_replace_recursive($existing, $new);
    }
}
