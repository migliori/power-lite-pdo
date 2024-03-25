<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo;

class PaginationOptions
{
    /**
     * Class PaginationOptions
     *
     * Represents the options for pagination.
     */

    /**
     * @var string $activeClass The CSS class for the active page.
     */
    public string $activeClass = 'active';

    /**
     * @var string $disabledClass The CSS class for the disabled page.
     */
    public string $disabledClass = 'disabled';

    /**
     * @var string $paginationClass The CSS class for the pagination container.
     */
    public string $paginationClass = 'pagination pagination-flat';

    /**
     * @var string $firstMarkup The markup for the "First" button.
     */
    public string $firstMarkup = '<i class="fas fa-angle-double-left"></i>';

    /**
     * @var string $previousMarkup The markup for the "Previous" button.
     */
    public string $previousMarkup = '<i class="fas fa-angle-left"></i>';

    /**
     * @var string $nextMarkup The markup for the "Next" button.
     */
    public string $nextMarkup = '<i class="fas fa-angle-right"></i>';

    /**
     * @var string $lastMarkup The markup for the "Last" button.
     */
    public string $lastMarkup = '<i class="fas fa-angle-double-right"></i>';

    /**
     * @var int $navLength The number of pages to display in the navigation.
     */
    public int $navLength = 2;

    /**
     * @var string $querystring The query string to append to the pagination links.
     * E.g.: 'p' for page.
     */
    public string $querystring = 'p';

    /**
     * @var bool $rewriteLinks Indicates whether to rewrite the pagination links.
     */
    public bool $rewriteLinks = false;

    /**
     * @var string $rewriteTransition The transition string used for rewriting the pagination links.
     */
    public string $rewriteTransition = '&';

    /**
     * @var string $rewriteExtension The file extension used for rewriting the pagination links.
     */
    public string $rewriteExtension = '';

    /**
     * Constructor for the PaginationOptions class.
     *
     * @param array<string, bool|string> $options An array of options to initialize the PaginationOptions object.
     */
    public function __construct(array $options = [])
    {
        $this->set($options);
    }

    /**
     * Set the pagination options.
     *
     * @param array<string, bool|string> $options The pagination options.
     */
    public function set(array $options): void
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
