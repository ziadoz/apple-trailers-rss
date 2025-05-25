<?php
readonly class RssFeed
{
    public function __construct(private string $url, private array $items)
    {
    }

    public function render(): string
    {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('    ');
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('rss');
        $writer->writeAttribute('version', '2.0');
        $writer->writeAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');

        $writer->startElement('channel');
        $writer->writeElement('title', 'Apple Trailers');
        $writer->writeElement('link', $this->url);
        $writer->writeElement('description', 'An RSS of Apple Trailers.');
        $writer->writeElement('language', 'en-us');
        $writer->writeElement('pubDate', 'Sat, 24th May 2025 18:00:00 +00:00:00');
        $writer->writeElement('lastBuildDate', new DateTime()->format(DATE_RSS));
        $writer->writeElement('docs', 'https://www.rssboard.org/rss-specification');

        $writer->startElement('atom:link');
        $writer->writeAttribute('href', $this->url);
        $writer->writeAttribute('rel', 'self');
        $writer->writeAttribute('type', 'application/rss+xml');
        $writer->endElement();

        foreach ($this->items as $item) {
            $writer->startElement('item');
            $writer->writeElement('title', $item['title']);
            $writer->writeElement('link', $item['link']);
            $writer->startElement('description');
            $writer->writeCdata(sprintf('%s: <a href="%s">%s</a><br><br>', $item['title'], $item['link'], $item['link']));
            $writer->writeCdata(sprintf('<img src="%s" alt="%s">', $item['image'], $item['title']));
            $writer->endElement();
            $writer->writeElement('pubDate', $item['pubDate']);
            $writer->writeElement('guid', $item['guid']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory(true);
    }
}