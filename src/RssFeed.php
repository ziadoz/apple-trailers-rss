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
            $writer->writeCdata(sprintf('<p>%s: <a href="%s">%s</a></p>', $item['title'], $item['link'], $item['link']));
            $writer->writeCdata(sprintf('<p><img src="%s" alt="%s"></p>', $item['image'], $item['title']));
            $writer->endElement();
            $writer->writeElement('guid', $item['guid']);
            $writer->endElement();
        }

        $writer->endElement();
        $writer->endElement();
        $writer->endDocument();

        return $writer->outputMemory(true);
    }
}