# Fusion

A CLI tool that helps teams manage AI guidelines, skills, and MCP server configurations for AI coding agents.

## Overview

Fusion compiles markdown files from a `.ai` folder into output files for various AI coding agents (Cursor, Copilot, Claude, OpenCode). It also manages MCP (Model Context Protocol) server configurations across these agents.

## Project Structure

```
src/
├── Agent.php                 # Enum: cursor, copilot, claude, opencode
├── McpServer.php             # Value object for MCP server config
├── FusionConfig.php          # Parses & merges fusion.yaml + fusion.local.yaml
├── EnvResolver.php           # Resolves ${VAR} syntax, throws on missing
├── ConfigMerger.php          # Deep merge utility for configs
├── Compilers/
│   ├── GuidelinesCompiler.php
│   ├── SkillsCompiler.php
│   └── McpCompiler.php       # Transforms MCP config to each agent's format
├── Writers/
│   ├── OutputWriter.php      # Writes guidelines/skills to agent files
│   ├── McpWriter.php         # Writes MCP JSON, merges with existing
│   └── GitignoreUpdater.php
└── Commands/
    ├── GenerateCommand.php   # Default command, orchestrates everything
    └── InitCommand.php       # Scaffolds .ai/ folder structure
```

## CLI Commands

```bash
# Run generate (default command), interactive if no config exists
./vendor/bin/fusion

# Scaffold .ai/ folder structure
./vendor/bin/fusion init

# Generate for specific agents only
./vendor/bin/fusion --cursor --copilot

# Custom config path
./vendor/bin/fusion -c ./custom/fusion.yaml

# Custom folder paths
./vendor/bin/fusion --ai-folder ./custom-ai/
./vendor/bin/fusion --guidelines-folder ./guides/
./vendor/bin/fusion --skills-folder ./skills/

# Additional output paths for guidelines/skills
./vendor/bin/fusion -p ./docs/ -p ./other/

# Update .gitignore with output files
./vendor/bin/fusion --update-gitignore
```

## Configuration

### `.ai/fusion.yaml` (project config, committed)

```yaml
ai_folder: .ai
guidelines_folder: .ai/guidelines
skills_folder: .ai/skills

cursor: true
copilot: true
claude: false
opencode: false

mcp:
  cursor: true
  copilot: true
  opencode: true
  
  servers:
    database:
      command: ["npx", "-y", "@modelcontextprotocol/server-postgres"]
      env:
        POSTGRES_CONNECTION_STRING: "${DATABASE_URL}"
    
    internal-docs:
      command: ["python", "-m", "docs_mcp_server"]
      cwd: ./tools/docs-mcp
    
    context7:
      url: https://mcp.context7.com/mcp
      headers:
        CONTEXT7_API_KEY: "${CONTEXT7_API_KEY}"

path:
  - ./docs/

update_gitignore: true
```

### `.ai/fusion.local.yaml` (local overrides, gitignored)

```yaml
mcp:
  servers:
    database:
      env:
        POSTGRES_CONNECTION_STRING: "postgresql://localhost:5432/mydb"
    
    my-notes:
      command: ["node", "~/tools/notes-mcp/index.js"]
    
    internal-docs:
      enabled: false
```

### Config Priority

CLI flags > Environment variables (`FUSION_*`) > Config file > Defaults

## MCP Server Schema

```yaml
servers:
  server-name:
    # Local server (has command)
    command: ["executable", "arg1", "arg2"]  # Required for local
    env:                                      # Optional
      KEY: "value"
    cwd: ./relative/path                      # Optional
    
    # Remote server (has url)
    url: https://example.com/mcp              # Required for remote
    headers:                                  # Optional
      Authorization: "Bearer ${API_KEY}"
    
    # Common
    enabled: true                             # Optional, default true
```

**Type inference:**
- Has `url` → remote server
- Has `command` → local server
- Has both → error
- Has neither → error

## Output Files

### Guidelines & Skills (not gitignored)

| Agent | Path |
|-------|------|
| Cursor | `.cursorrules` |
| Copilot | `.github/copilot-instructions.md` |
| Claude | `CLAUDE.md` |
| OpenCode | `AGENTS.md` |

**Output format:**
```markdown
<fusion-guidelines>

=== Guidelines ===

{compiled guidelines, alphabetically, including local/ files}

=== Skills ===

{compiled skills, alphabetically, including local/ files}

</fusion-guidelines>
```

### MCP Configs (gitignored - contain resolved secrets)

| Agent | Path | Format |
|-------|------|--------|
| Cursor | `.cursor/mcp.json` | `{ "mcpServers": { ... } }` |
| VS Code/Copilot | `.vscode/mcp.json` | `{ "servers": { ... } }` |
| OpenCode | `opencode.json` | `{ "mcp": { ... } }` (merged with existing) |

## Folder Structure Created by `fusion init`

```
.ai/
├── fusion.yaml              # Project config (committed)
├── fusion.local.yaml        # Developer overrides (gitignored via entry in fusion.yaml)
├── guidelines/
│   ├── project.md           # Example guideline (committed)
│   └── local/
│       └── .gitignore       # Contains: *\n!.gitignore
└── skills/
    ├── code-review.md       # Example skill (committed)
    └── local/
        └── .gitignore       # Contains: *\n!.gitignore
```

## Key Behaviors

1. **Fail fast** - Stop on first error (missing env var, invalid config, etc.)
2. **Deep merge** - `fusion.local.yaml` deep-merges into `fusion.yaml` (local values win)
3. **Env var resolution** - `${VAR}` resolved at generation time, error if missing
4. **Disabled servers omitted** - Servers with `enabled: false` are not included in output
5. **Preserve existing configs** - When writing `opencode.json` or `.vscode/mcp.json`, merge only relevant keys, preserve the rest
6. **Alphabetical ordering** - Guidelines and skills are sorted alphabetically in output

## Agent-Specific MCP Format Translation

### Cursor (`.cursor/mcp.json`)
```json
{
  "mcpServers": {
    "database": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-postgres"],
      "env": {
        "POSTGRES_CONNECTION_STRING": "resolved-value"
      }
    },
    "context7": {
      "url": "https://mcp.context7.com/mcp",
      "headers": {
        "CONTEXT7_API_KEY": "resolved-value"
      }
    }
  }
}
```

### VS Code/Copilot (`.vscode/mcp.json`)
```json
{
  "servers": {
    "database": {
      "command": "npx",
      "args": ["-y", "@modelcontextprotocol/server-postgres"],
      "env": {
        "POSTGRES_CONNECTION_STRING": "resolved-value"
      }
    },
    "context7": {
      "type": "http",
      "url": "https://mcp.context7.com/mcp",
      "headers": {
        "CONTEXT7_API_KEY": "resolved-value"
      }
    }
  }
}
```

### OpenCode (`opencode.json`)
```json
{
  "mcp": {
    "database": {
      "type": "local",
      "command": ["npx", "-y", "@modelcontextprotocol/server-postgres"],
      "environment": {
        "POSTGRES_CONNECTION_STRING": "resolved-value"
      }
    },
    "context7": {
      "type": "remote",
      "url": "https://mcp.context7.com/mcp",
      "headers": {
        "CONTEXT7_API_KEY": "resolved-value"
      }
    }
  }
}
```

## Dependencies

### Runtime
- `symfony/console` - CLI framework
- `symfony/yaml` - YAML parsing
- `laravel/prompts` - Interactive prompts (with Symfony fallback for Windows)

### Development
- `pestphp/pest` - Testing
- `zenstruck/console-test` - Console command testing
- `mockery/mockery` - Mocking
- `laravel/pint` - Code style
- `phpstan/phpstan` - Static analysis

## Implementation Phases

### Phase 1: Project Setup
- Add dev dependencies
- Configure Pest
- GitHub Actions CI

### Phase 2: Core Classes
1. `Agent` enum
2. `McpServer` value object
3. `FusionConfig` - parses & merges configs
4. `EnvResolver` - resolves `${VAR}`, throws on missing
5. `ConfigMerger` - deep merge utility

### Phase 3: Compilers
1. `GuidelinesCompiler`
2. `SkillsCompiler`
3. `McpCompiler` - transforms to each agent format

### Phase 4: Writers
1. `OutputWriter` - writes guidelines/skills to agent files
2. `McpWriter` - writes MCP JSON, merges with existing
3. `GitignoreUpdater`

### Phase 5: Commands
1. `GenerateCommand` (default)
2. `InitCommand` - scaffolds `.ai/` structure

### Phase 6: Testing
- Unit tests per class
- Integration tests for generate flow
