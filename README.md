# Project: Web Crawler and Search

## Crawler (`crawler.php`)

### Description

The `crawler.php` script is designed to recursively fetch HTML content from a specified URL and its linked pages up to a certain depth. It adheres to `robots.txt` rules to avoid crawling disallowed paths. The fetched HTML content is saved in an output directory.

### Usage

- Set the target URL and the depth of crawling.
  ```php
  $url = "https://www.w3schools.com/php/";
  $depth = 2;
  ```

- Clean the output directory to remove any existing files.
  ```php
  $output_dir = "fetched/";
  $files = glob($output_dir . '*');
  foreach ($files as $file) {
      if (is_file($file)) {
          unlink($file);
      }
  }
  ```

- Parse `robots.txt` to get disallowed URLs and initialize the URL queue.

- Start crawling and save HTML content to files.

## Search (`search.php`)

### Description

The `search.php` script performs a basic search operation on the HTML content previously fetched by the crawler. It extracts text from various HTML elements and calculates the relevance score using cosine similarity. The top N relevant documents are then displayed, with the search query highlighted.

### Usage

- Set the search query and the number of top results to display.

- Fetch HTML content from the output directory and process it.
  
- Calculate cosine similarity scores and get the top N results.
  
- Highlight the search query in the top result and print the result.

### Note

These scripts use PHP, cURL, and DOMDocument to perform web crawling and basic search operations. Make sure to set the appropriate permissions and dependencies for the execution of these scripts.