<?php
readonly class HtmlDownloader
{
    public function __construct(private string $url)
    {
    }

    public function download(): string
    {
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->url,
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
}