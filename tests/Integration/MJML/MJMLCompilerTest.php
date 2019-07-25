<?php


namespace Tests\Integration\MJML;


use Tests\BaseTestCase;

class MJMLCompilerTest extends BaseTestCase
{
    private const INCLUDE_FAIL_REGEXP = '/mj-include fails to read file : [a-zA-Z\.\/\-]+ at [a-zA-Z\.\/\-]+/';


    public function testMJML()
    {
        $renderedMails = collect(glob(resource_path('views/mails/*')));
        $includesFailed = [];

        $renderedMails->each(function (string $renderedEmail) use (&$includesFailed) {
            $content = file_get_contents($renderedEmail);
            preg_match_all(self::INCLUDE_FAIL_REGEXP, $content, $matches);

            if (count($matches) > 0) {
                $includesFailed[$renderedEmail] = $matches;
            }
        });

        $message = [];

        foreach ($includesFailed as $sourceRenderedTemplate => $matches) {
            foreach ($matches[0] as $error) {
                $message[] = str_replace(base_path(), '', "$sourceRenderedTemplate: $error");
            }
        }

        $this->assertEmpty($message, "Some rendered files contain <mj-include> errors!\n" . implode("\n", $message));
    }
}
