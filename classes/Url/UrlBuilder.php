<?php

declare(strict_types=1);

namespace KirbyCollectionManager\Url;

use Kirby\Cms\Page;

/**
 * URL Builder for Collection Manager
 *
 * Builds page URLs that preserve the params owned by a collection instance
 * (search, sort, filters, pagination) and nothing else, so that unrelated
 * query params are never reflected into generated links.
 */
final class UrlBuilder
{
    /**
     * The base page for URL generation
     */
  private Page $page;

    /**
     * The params owned by the collection instance
     */
  private array $knownParams;

  public function __construct(Page $page, array $knownParams)
  {
    $this->page = $page;
    $this->knownParams = $knownParams;
  }

    /**
     * Build a URL from the current state of the known params.
     *
     * @param array $overrides Params to set (null or '' removes the param)
     */
  public function build(array $overrides = []): string
  {
    $params = [];

    foreach ($this->knownParams as $param) {
      $value = get($param);
      if ($value !== null && $value !== '') {
        $params[$param] = $value;
      }
    }

    $params = array_merge($params, $overrides);
    $params = array_filter($params, fn ($value) => $value !== null && $value !== '');

    $url = $this->page->url();

    if ($params === []) {
      return $url;
    }

    return $url . '?' . http_build_query($params);
  }

    /**
     * Get the base page
     */
  public function getPage(): Page
  {
    return $this->page;
  }

    /**
     * Get the known params list
     */
  public function getKnownParams(): array
  {
    return $this->knownParams;
  }
}
