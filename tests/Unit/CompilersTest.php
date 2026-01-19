<?php

use Myleshyson\Fusion\Compilers\GuidelinesCompiler;
use Myleshyson\Fusion\Compilers\SkillsCompiler;

beforeEach(function () {
    $this->artifactPath = __DIR__.'/../artifacts/compilers';
    cleanDirectory($this->artifactPath);
    mkdir($this->artifactPath, 0777, true);
});

afterEach(function () {
    cleanDirectory($this->artifactPath);
});

// ==================== GuidelinesCompiler Tests ====================

describe('GuidelinesCompiler', function () {
    it('returns empty string when directory does not exist', function () {
        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/nonexistent");
        expect($result)->toBe('');
    });

    it('returns empty string when directory has no markdown files', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        file_put_contents("{$this->artifactPath}/guidelines/readme.txt", 'Not markdown');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");
        expect($result)->toBe('');
    });

    it('compiles single markdown file', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        file_put_contents("{$this->artifactPath}/guidelines/test.md", '# Test Guideline');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");
        expect($result)->toBe('# Test Guideline');
    });

    it('compiles multiple files sorted alphabetically', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        // Create files in reverse alphabetical order to ensure ksort is tested
        file_put_contents("{$this->artifactPath}/guidelines/z-last.md", '# Last');
        file_put_contents("{$this->artifactPath}/guidelines/a-first.md", '# First');
        file_put_contents("{$this->artifactPath}/guidelines/m-middle.md", '# Middle');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");

        // The exact order matters - must be alphabetically sorted
        expect($result)->toBe("# First\n\n# Middle\n\n# Last");
    });

    it('sorts files alphabetically regardless of creation order', function () {
        // Create a subdirectory with numeric prefix to test sorting more explicitly
        mkdir("{$this->artifactPath}/guidelines-sort", 0777, true);
        // Create in non-alphabetical order: 3, 1, 2
        file_put_contents("{$this->artifactPath}/guidelines-sort/3-third.md", 'Third');
        file_put_contents("{$this->artifactPath}/guidelines-sort/1-first.md", 'First');
        file_put_contents("{$this->artifactPath}/guidelines-sort/2-second.md", 'Second');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines-sort");

        // Result must be in alphabetical order by filename
        expect($result)->toBe("First\n\nSecond\n\nThird");
    });

    it('skips empty files', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        file_put_contents("{$this->artifactPath}/guidelines/empty.md", '');
        file_put_contents("{$this->artifactPath}/guidelines/whitespace.md", '   ');
        file_put_contents("{$this->artifactPath}/guidelines/real.md", '# Real Content');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");
        expect($result)->toBe('# Real Content');
    });

    it('trims whitespace from content', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        file_put_contents("{$this->artifactPath}/guidelines/test.md", "  \n# Test\n  ");

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");
        expect($result)->toBe('# Test');
    });

    it('ignores .gitignore files', function () {
        mkdir("{$this->artifactPath}/guidelines", 0777, true);
        file_put_contents("{$this->artifactPath}/guidelines/.gitignore", '*');
        file_put_contents("{$this->artifactPath}/guidelines/test.md", '# Test');

        $compiler = new GuidelinesCompiler;
        $result = $compiler->compile("{$this->artifactPath}/guidelines");
        expect($result)->toBe('# Test');
    });
});

// ==================== SkillsCompiler Tests ====================

describe('SkillsCompiler', function () {
    it('returns empty array when directory does not exist', function () {
        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/nonexistent");
        expect($result)->toBe([]);
    });

    it('returns empty array when directory has no markdown files', function () {
        mkdir("{$this->artifactPath}/skills", 0777, true);
        file_put_contents("{$this->artifactPath}/skills/readme.txt", 'Not markdown');

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills");
        expect($result)->toBe([]);
    });

    it('compiles single markdown file', function () {
        mkdir("{$this->artifactPath}/skills", 0777, true);
        file_put_contents("{$this->artifactPath}/skills/test.md", '# Test Skill');

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills");
        expect($result)->toBe(['test.md' => '# Test Skill']);
    });

    it('compiles multiple files sorted alphabetically', function () {
        mkdir("{$this->artifactPath}/skills", 0777, true);
        file_put_contents("{$this->artifactPath}/skills/z-skill.md", '# Z Skill');
        file_put_contents("{$this->artifactPath}/skills/a-skill.md", '# A Skill');

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills");

        expect(array_keys($result))->toBe(['a-skill.md', 'z-skill.md']);
        expect($result['a-skill.md'])->toBe('# A Skill');
        expect($result['z-skill.md'])->toBe('# Z Skill');
    });

    it('sorts files alphabetically regardless of creation order', function () {
        mkdir("{$this->artifactPath}/skills-sort", 0777, true);
        // Create in non-alphabetical order: 3, 1, 2
        file_put_contents("{$this->artifactPath}/skills-sort/3-third.md", 'Third');
        file_put_contents("{$this->artifactPath}/skills-sort/1-first.md", 'First');
        file_put_contents("{$this->artifactPath}/skills-sort/2-second.md", 'Second');

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills-sort");

        // Array keys must be in alphabetical order
        expect(array_keys($result))->toBe(['1-first.md', '2-second.md', '3-third.md']);
        // Verify values match the sorted order
        expect(array_values($result))->toBe(['First', 'Second', 'Third']);
    });

    it('skips empty files', function () {
        mkdir("{$this->artifactPath}/skills", 0777, true);
        file_put_contents("{$this->artifactPath}/skills/empty.md", '');
        file_put_contents("{$this->artifactPath}/skills/whitespace.md", '   ');
        file_put_contents("{$this->artifactPath}/skills/real.md", '# Real');

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills");
        expect($result)->toBe(['real.md' => '# Real']);
    });

    it('trims whitespace from content', function () {
        mkdir("{$this->artifactPath}/skills", 0777, true);
        file_put_contents("{$this->artifactPath}/skills/test.md", "  \n# Test\n  ");

        $compiler = new SkillsCompiler;
        $result = $compiler->compile("{$this->artifactPath}/skills");
        expect($result['test.md'])->toBe('# Test');
    });
});
