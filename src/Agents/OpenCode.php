<?php

namespace Myleshyson\Fusion\Agents;

class OpenCode extends BaseAgent
{
    public static function optionName(): string
    {
        return 'opencode';
    }

    public function name(): string
    {
        return 'OpenCode';
    }

    /**
     * OpenCode can be detected via multiple paths.
     *
     * @return array<string>
     */
    public function detectionPaths(): array
    {
        return [
            'AGENTS.md',
            'opencode.json',
            '.opencode/',
        ];
    }

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
        return 'opencode.json';
    }

    protected function transformMcpConfig(array $servers): array
    {
        $mcpConfig = [];

        foreach ($servers as $name => $config) {
            if (! is_array($config)) {
                continue;
            }

            $server = [];

            if (isset($config['command'])) {
                // Local server
                $server['type'] = 'local';
                $server['command'] = $config['command'];
                if (isset($config['env'])) {
                    $server['environment'] = $config['env'];
                }
            } elseif (isset($config['url'])) {
                // Remote server
                $server['type'] = 'remote';
                $server['url'] = $config['url'];
                if (isset($config['headers'])) {
                    $server['headers'] = $config['headers'];
                }
            }

            $mcpConfig[$name] = $server;
        }

        return ['mcp' => $mcpConfig];
    }

    protected function mergeMcpConfig(array $existing, array $new): array
    {
        // Merge mcp key specifically, preserving other opencode.json settings
        $existingMcp = isset($existing['mcp']) && is_array($existing['mcp']) ? $existing['mcp'] : [];
        $newMcp = isset($new['mcp']) && is_array($new['mcp']) ? $new['mcp'] : [];

        if (! empty($existingMcp) && ! empty($newMcp)) {
            $new['mcp'] = array_replace_recursive($existingMcp, $newMcp);
        }

        return array_replace_recursive($existing, $new);
    }
}
