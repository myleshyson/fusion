<?php

namespace Myleshyson\Mush\Contracts;

interface SkillsSupport
{
    /**
     * Get the path where skills should be written.
     */
    public function path(): string;

    /**
     * Write skills to the skills path.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $skills  Map of skill-name => skill data
     */
    public function write(array $skills): void;

    /**
     * Remove skills that are no longer in the source.
     *
     * @param  array<string, array{name: string, description: string, content: string}>  $currentSkills  Map of skill-name => skill data that should exist
     */
    public function cleanup(array $currentSkills): void;
}
