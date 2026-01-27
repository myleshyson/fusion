<?php

namespace Myleshyson\Mush\Agents\Concerns;

trait CleanupDirectoryItems
{
    /**
     * Remove skill directories that are no longer in the source.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $currentItems
     */
    protected function cleanupSkillDirectories(string $basePath, array $currentItems): void
    {
        if (! is_dir($basePath)) {
            return;
        }

        // Get all existing skill directories
        $existingDirs = glob(rtrim($basePath, '/').'/*', GLOB_ONLYDIR);

        if ($existingDirs === false) {
            return;
        }

        foreach ($existingDirs as $itemDir) {
            $itemName = basename($itemDir);

            // If this item is not in the current items, remove it
            if (! isset($currentItems[$itemName])) {
                $this->removeDirectory($itemDir);
            }
        }
    }

    /**
     * Remove markdown files that are no longer in the source.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $currentItems
     */
    protected function cleanupMarkdownFiles(string $basePath, array $currentItems): void
    {
        if (! is_dir($basePath)) {
            return;
        }

        // Get all existing markdown files
        $existingFiles = glob(rtrim($basePath, '/').'/*.md');

        if ($existingFiles === false) {
            return;
        }

        foreach ($existingFiles as $itemFile) {
            $itemName = pathinfo($itemFile, PATHINFO_FILENAME);

            // If this item is not in the current items, remove it
            if (! isset($currentItems[$itemName])) {
                unlink($itemFile);
            }
        }
    }

    /**
     * Remove prompt files (.prompt.md) that are no longer in the source.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $currentItems
     */
    protected function cleanupPromptFiles(string $basePath, array $currentItems): void
    {
        if (! is_dir($basePath)) {
            return;
        }

        // Get all existing .prompt.md files
        $existingFiles = glob(rtrim($basePath, '/').'/*.prompt.md');

        if ($existingFiles === false) {
            return;
        }

        foreach ($existingFiles as $itemFile) {
            // Remove .prompt.md extension to get the item name
            $basename = basename($itemFile);
            $itemName = str_replace('.prompt.md', '', $basename);

            // If this item is not in the current items, remove it
            if (! isset($currentItems[$itemName])) {
                unlink($itemFile);
            }
        }
    }

    /**
     * Remove TOML files (.toml) that are no longer in the source.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $currentItems
     */
    protected function cleanupTomlFiles(string $basePath, array $currentItems): void
    {
        if (! is_dir($basePath)) {
            return;
        }

        // Get all existing .toml files
        $existingFiles = glob(rtrim($basePath, '/').'/*.toml');

        if ($existingFiles === false) {
            return;
        }

        foreach ($existingFiles as $itemFile) {
            // Remove .toml extension to get the item name
            $basename = basename($itemFile);
            $itemName = str_replace('.toml', '', $basename);

            // If this item is not in the current items, remove it
            if (! isset($currentItems[$itemName])) {
                unlink($itemFile);
            }
        }
    }

    /**
     * Recursively remove a directory and its contents.
     */
    protected function removeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $itemPath = $path.'/'.$item;

            if (is_dir($itemPath)) {
                $this->removeDirectory($itemPath);
            } else {
                unlink($itemPath);
            }
        }

        rmdir($path);
    }
}
