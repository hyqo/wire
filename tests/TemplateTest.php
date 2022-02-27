<?php

namespace Hyqo\Wire\Test;

use Hyqo\Wire\Template;
use PHPUnit\Framework\TestCase;

use function Hyqo\Wire\render_template;

class TemplateTest extends TestCase
{
    public function setUp(): void
    {
        $this->cleanup(__DIR__ . '/cache');

        Template::setTemplatesDir(__DIR__ . '/fixtures/input');
        Template::setCacheDir(__DIR__ . '/cache/templates');
    }

    protected function cleanup(string $dir): void
    {
        foreach (glob($dir . '/*') as $entry) {
            if (is_dir($entry)) {
                $this->cleanup($entry);
                rmdir($entry);
            } else {
                unlink($entry);
            }
        }
    }

    /** @dataProvider provideRenderData */
    public function test_render(string $name, string $output): void
    {
        $compiled = render_template($name);

        $this->assertEquals(rtrim($output), $compiled);
    }

    public function provideRenderData(): \Generator
    {
        $dir = __DIR__ . '/fixtures';

        foreach (glob("$dir/input/*") as $file) {
            if (is_dir($file)) {
                continue;
            }

            $filename = pathinfo($file, PATHINFO_FILENAME);

            yield [
                $filename,
                file_get_contents("$dir/output/" . $filename . '.html'),
            ];
        }
    }
}
