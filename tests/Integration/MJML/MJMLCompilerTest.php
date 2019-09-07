<?php


namespace Tests\Integration\MJML;


use Tests\BaseTestCase;

class MJMLCompilerTest extends BaseTestCase
{
    private const INCLUDE_FAIL_REGEXP = '/mj-include fails to read file : [a-zA-Z\.\/\-]+ at [a-zA-Z\.\/\-]+/';


    public function MJMLDataProvider()
    {
        $files = $renderedMails = glob(resource_path('views/mails/*'));
        $data = [];

        foreach ($files as $file) {
            $key = basename($file);
            $data[$key] = [$file];
        }

        return $data;
    }


    /**
     * Assert the a rendered MJML file does not contain any errors.
     *
     * @param string $file The MJML rendered blade file.
     *
     * @dataProvider MJMLDataProvider
     */
    public function testMJML(string $file): void
    {
        $content = file_get_contents($file);
        preg_match_all(self::INCLUDE_FAIL_REGEXP, $content, $matches);

        $errors = [];

        foreach ($matches[0] as $error) {
            $errors[] = str_replace(base_path(), '', "$file: $error");
        }

        $this->assertEmpty(
            $errors,
            "Some rendered files contain <mj-include> errors!\n" . implode("\n", $errors)
        );
    }
}
