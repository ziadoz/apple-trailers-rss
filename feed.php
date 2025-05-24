<?php
use Dom\HTMLDocument;

class AppleTrailersRss
{
    private const string URL = 'https://apple.com/trailers';

    private const string FILM_SELECTOR       = '.infinite-grid__body > div';
    private const string FILM_TITLE_SELECTOR = '.canvas-lockup > a[aria-label]';
    private const string FILM_LINK_SELECTOR  = '.canvas-lockup > a[aria-label]';
    private const string FILM_IMAGE_SELECTOR = '.canvas-lockup > a[aria-label] .media-artwork-v2__image';

    private const FILM_TITLE_ATTR = 'aria-label';
    private const FILM_LINK_ATTR  = 'href';
    private const FILM_IMAGE_ATTR = 'src';

    private function getHtml(): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => static::URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
        ]);

        $html = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new RuntimeException('Error downloading Apple Trailers HTML: ' . curl_error($ch));
        }

        $code = (string) curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (str_starts_with($code, '4') || str_starts_with($code, '5')) {
            throw new RuntimeException('HTTP error downloading Apple Trailers HTML: ' . $code);
        }

        return $html;
    }

    private function parseHtml(string $html): array
    {
        try {
            set_error_handler(function (int $severity, string $message, string $file, int $line): never {
                throw new RuntimeException(
                    'Error parsing Apple Trailers HTML',
                    previous: new ErrorException($message, 0, $severity, $file, $line),
                );
            });

            $html = HTMLDocument::createFromString($html, LIBXML_HTML_NOIMPLIED);
        } finally {
            restore_error_handler();
        }

        $items = [];

        foreach ($html->querySelectorAll(static::FILM_SELECTOR) as $film) {
            $title = $film->querySelector(static::FILM_TITLE_SELECTOR)->getAttribute(static::FILM_TITLE_ATTR);
            $link  = static::URL . $film->querySelector(static::FILM_LINK_SELECTOR)->getAttribute(static::FILM_LINK_ATTR);
            $image = static::URL . $film->querySelector(static::FILM_IMAGE_SELECTOR)->getAttribute(static::FILM_IMAGE_ATTR);
            $guid  = hash('xxh128', $link);

            if (empty($title)) {
                continue;
            }

            $items[] = [
                'title'       => $title,
                'link'        => $link,
                'description' => sprintf('<img src="%s" alt="%s">', $image, $title),
                'pubDate'     => new DateTime()->format(DATE_RSS),
                'guid'        => $guid,
            ];
        }

        return $items;
    }

    private function buildRss(array $items): string
    {
        $xml = '';

        foreach ($items as $item) {
            $xml .= <<<RSS
            <item>
                <title>{$item['title']}</title>
                <link>{$item['link']}</link>
                <description>{$item['description']}</description>
                <pubDate>{$item['pubDate']}</pubDate>
                <guid>{$item['guid']}</guid>
            </item>
            RSS;
        }

        return <<<RSS
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
            <channel>
                <title>Apple Trailers</title>
                <link>{static::URL}</link>
                <description>A RSS containing the latest film and TV trailers from Apple Trailers.</description>
                <language>en-us</language>
                <pubDate>Sat, 24th May 2025 18:00:00 +00:00:00</pubDate>
                <lastBuildDate>{new DateTime()->format(DATE_RSS)}</lastBuildDate>
                <docs>https://www.rssboard.org/rss-specification</docs>
                <atom:link href="#" rel="self" type="application/rss+xml"/>
                {$xml}
           </channel>
        </rss>
        RSS;
    }

    public function getRss(): string
    {
        return $this->buildRss(
            $this->parseHtml(
                $this->getHtml()
            )
        );
    }
}

file_put_contents(
    'feed.rss',
    new AppleTrailersRss()->getRss(),
);
