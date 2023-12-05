<?php

function fetchRobotsTxt($url)
{
    $baseUrl = rtrim($url, '/');
    $robotsTxtUrl = $baseUrl . '/robots.txt';

    $ch = curl_init($robotsTxtUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $robotsTxtContent = curl_exec($ch);
    curl_close($ch);

    if ($robotsTxtContent === false) {
        return [];
    }

    $disallowedLinks = [];
    $lines = explode("\n", $robotsTxtContent);

    foreach ($lines as $line) {
        $line = trim($line);

        if (strpos($line, 'Disallow:') === 0) {
            $disallowedPath = trim(substr($line, strlen('Disallow:')));
            $disallowedLink = $baseUrl . $disallowedPath;
            $disallowedLinks[] = $disallowedLink;
        }
    }

    return $disallowedLinks;
}

function fetchHtmlContent($url)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $htmlContent = curl_exec($ch);
    curl_close($ch);

    return $htmlContent;
}

function saveHtmlToFile($outputDir, $depth, $htmlContent)
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($htmlContent);
    libxml_use_internal_errors(false);

    $htmlContent = $dom->saveHTML();
    file_put_contents($outputDir . $depth . ".html", $htmlContent);
}

$url = "https://www.w3schools.com/php/";
$depth = 2;
$outputDir = "fetched/";

$completedUrls = [];
$disallowedUrls = fetchRobotsTxt($url);

$urlsToScrap = [$url];

while ($depth > 0 && !empty($urlsToScrap)) {
    $currentUrl = array_pop($urlsToScrap);

    if (in_array($currentUrl, $completedUrls)) {
        continue;
    }

    $htmlContent = fetchHtmlContent($currentUrl);

    if ($htmlContent === false || trim($htmlContent) == "" || stripos($htmlContent, 'PAGE NOT FOUND') !== false || curl_getinfo($ch, CURLINFO_HTTP_CODE) == 404) {
        continue;
    }

    saveHtmlToFile($outputDir, $depth, $htmlContent);

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($htmlContent);
    libxml_use_internal_errors(false);

    $hrefs = array();

    $anchorTags = $dom->getElementsByTagName('a');

    foreach ($anchorTags as $anchor) {
        $href = $anchor->getAttribute('href');
        $hrefs[] = $href;
    }

    $moreUrls = array_filter($hrefs, function ($url) {
        return !(strpos($url, '#') === 0 || filter_var($url, FILTER_VALIDATE_URL) === false);
    });

    $moreUrls = array_map(function ($url) use ($baseUrl) {
        return strpos($url, '/') === 0 ? rtrim($baseUrl, '/') . $url : $url;
    }, $moreUrls);

    $moreUrlsFiltered = array_diff($moreUrls, $completedUrls);
    $moreUrlsFiltered = array_values($moreUrlsFiltered);

    foreach ($disallowedUrls as $disallowedLink) {
        $moreUrlsFiltered = array_filter($moreUrlsFiltered, function ($url) use ($disallowedLink) {
            $regexPattern = str_replace(['*', '/'], ['.*', '\/'], $disallowedLink);
            return !preg_match('/^' . $regexPattern . '$/', $url);
        });
    }

    $urlsToScrap = array_merge($urlsToScrap, $moreUrlsFiltered);
    $depth--;
    $completedUrls[] = $currentUrl;
}
?>
