<?php

declare(strict_types=1);

namespace Mathrix\Mailjet;

use Illuminate\Mail\Mailable;
use Swift_Message;
use const JSON_THROW_ON_ERROR;
use function json_encode;

/**
 * Base mailable for Mailjet template-based emails.
 */
class MailjetTemplateMailable extends Mailable
{
    /**
     * @var int The Mailjet template id.
     */
    protected int $templateId;

    /**
     * Hijack the Laravel renderer since we do not need to render anything (that is Mailjet's job).
     *
     * @return array
     */
    protected function buildView(): array
    {
        return [null, null, 'raw'];
    }

    /**
     * Register Mailjet's headers.
     */
    public function build(): void
    {
        $this->withSwiftMessage(function (Swift_Message $message) {
            // Enable template language
            $message->getHeaders()->addTextHeader('X-MJ-TemplateID', $this->templateId);
            $message->getHeaders()->addTextHeader('X-MJ-TemplateLanguage', true);

            // Encode email variables
            $mailjetVariables = json_encode($this->buildViewData(), JSON_THROW_ON_ERROR, 512);
            $message->getHeaders()->addTextHeader('X-MJ-Vars', $mailjetVariables);
        });
    }
}
