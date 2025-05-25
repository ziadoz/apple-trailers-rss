<?php
use Dom\HTMLDocument;
use Dom\HTMLElement;

readonly class HtmlParser
{
    private const string FILM_SELECTOR       = '.infinite-grid__body > div';
    private const string FILM_TITLE_SELECTOR = '.canvas-lockup > a[aria-label]';
    private const string FILM_LINK_SELECTOR  = '.canvas-lockup > a[aria-label]';
    private const string FILM_IMAGE_SELECTOR = '.canvas-lockup picture source[type="image/jpeg"]';

    private const string FILM_TITLE_ATTR = 'aria-label';
    private const string FILM_LINK_ATTR  = 'href';
    private const string FILM_IMAGE_ATTR = 'srcset';

    public function __construct(readonly private string $html)
    {
    }

    public function parse(): array
    {
        $items = [];

        foreach ($this->loadHtml()->querySelectorAll(static::FILM_SELECTOR) as $element) {
            $title = $this->parseTitle($element);
            $link  = $this->parseLink($element);
            $image = $this->parseImage($element);

            $items[] = [
                'title'   => $title,
                'link'    => $link,
                'image'   => $image,
                'pubDate' => new DateTime()->format(DATE_RSS),
                'guid'    => hash('xxh128', $link),
            ];
        }

        return $items;
    }

    private function loadHtml(): HTMLDocument
    {
        try {
            set_error_handler(function (int $severity, string $message, string $file, int $line): never {
                throw new RuntimeException(
                    'Error parsing Apple Trailers HTML',
                    previous: new ErrorException($message, 0, $severity, $file, $line),
                );
            });

            return HTMLDocument::createFromString($this->html, LIBXML_HTML_NOIMPLIED);
        } finally {
            restore_error_handler();
        }
    }

    private function parseTitle(HTMLElement $element): string
    {
        return $element->querySelector(static::FILM_TITLE_SELECTOR)->getAttribute(static::FILM_TITLE_ATTR);
    }

    private function parseLink(HTMLElement $element): string
    {
        return 'https://tv.apple.com' . $element->querySelector(static::FILM_LINK_SELECTOR)->getAttribute(static::FILM_LINK_ATTR);
    }

    private function parseImage(HTMLElement $element): string
    {
        $images = explode(',', $element->querySelector(static::FILM_IMAGE_SELECTOR)->getAttribute(static::FILM_IMAGE_ATTR));

        return preg_replace('/ \d{3}w$/', '', end($images));
    }
}