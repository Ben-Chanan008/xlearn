<?php

namespace App\Http\Requests\Concerns;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

trait SanitizesLessonHtml
{
    /**
     * Instructor HTML is shown to students, so only the tags the editor's toolbar makes are kept
     */
    protected function sanitizeLessonHtml(): void
    {
        $sanitizer = new HtmlSanitizer(
            (new HtmlSanitizerConfig())
                ->allowElement('p')
                ->allowElement('br')
                ->allowElement('h2')
                ->allowElement('h3')
                ->allowElement('strong')
                ->allowElement('em')
                ->allowElement('u')
                ->allowElement('s')
                ->allowElement('ol')
                ->allowElement('ul')
                ->allowElement('li')
                ->allowElement('blockquote')
                ->allowElement('pre')
                ->allowElement('code')
                ->allowElement('a', ['href'])
                ->allowLinkSchemes(['http', 'https', 'mailto'])
                ->forceAttribute('a', 'target', '_blank')
                ->forceAttribute('a', 'rel', 'noopener noreferrer')
        );

        $contents = collect($this->input('contents', []))->map(function ($content) use ($sanitizer) {
            $html = $sanitizer->sanitize($content['content_information'] ?? '');
            // An empty editor still sends <p><br></p>, which counts as no text
            $content['content_information'] = trim(strip_tags($html)) === '' ? null : $html;

            return $content;
        });

        $this->merge(['contents' => $contents->all()]);
    }
}
