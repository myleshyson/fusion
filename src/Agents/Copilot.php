<?php

namespace Myleshyson\Fusion\Agents;

class Copilot extends BaseAgent
{
    public static function optionName(): string
    {
        return 'copilot';
    }

    public function name(): string
    {
        return 'GitHub Copilot';
    }

    /**
     * Copilot can be detected via multiple paths.
     *
     * @return array<string>
     */
    public function detectionPaths(): array
    {
        return [
            '.github/copilot-instructions.md',
            '.vscode/mcp.json',
            '.github/',
        ];
    }

    public function guidelinesPath(): string
    {
        return '.github/copilot-instructions.md';
    }

    public function skillsPath(): string
    {
        return '.github/skills/';
    }

    public function mcpPath(): string
    {
        return '.vscode/mcp.json';
    }

    protected function transformMcpConfig(array $servers): array
    {
        // VS Code/Copilot uses "servers" key with "type": "http" for remote
        $mcpServers = [];

        foreach ($servers as $name => $config) {
            if (! is_array($config)) {
                continue;
            }

            $server = [];

            if (isset($config['command'])) {
                $command = $config['command'];
                $server['command'] = is_array($command) ? $command[0] : $command;
                if (is_array($command) && count($command) > 1) {
                    $server['args'] = array_slice($command, 1);
                }
                if (isset($config['env'])) {
                    $server['env'] = $config['env'];
                }
            } elseif (isset($config['url'])) {
                $server['type'] = 'http';
                $server['url'] = $config['url'];
                if (isset($config['headers'])) {
                    $server['headers'] = $config['headers'];
                }
            }

            $mcpServers[$name] = $server;
        }

        return ['servers' => $mcpServers];
    }

    protected function mergeMcpConfig(array $existing, array $new): array
    {
        $existingServers = isset($existing['servers']) && is_array($existing['servers']) ? $existing['servers'] : [];
        $newServers = isset($new['servers']) && is_array($new['servers']) ? $new['servers'] : [];

        if (! empty($existingServers) && ! empty($newServers)) {
            $new['servers'] = array_replace_recursive($existingServers, $newServers);
        }

        return array_replace_recursive($existing, $new);
    }
}
