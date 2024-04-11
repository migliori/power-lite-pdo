<?php

declare(strict_types=1);

namespace Migliori\PowerLitePdo;

use Exception;
use PDO;
use Migliori\PowerLitePdo\Db;
use Migliori\PowerLitePdo\Exception\PaginationException;
use Migliori\PowerLitePdo\PaginationOptions;
use Migliori\PowerLitePdo\View\View;

/**
 * Pagination class
 *
 * This class is used to generate pagination links for a given set of results.
 *
 */
class Pagination
{
    /**
     * Database connection object.
     */
    private Db $db;

    /**
     * Options
     * @var PaginationOptions $options The options for the pagination.
     */
    private PaginationOptions $paginationOptions;

    /**
     * Represents a Pagination object.
     *
     * @var View $view The view object associated with the Pagination.
     */
    private View $view;

    /**
     * Represents the current number of records in the pagination.
     */
    private int $currentNumberOfRecords;

    /**
     * Represents the current page number.
     *
     * @var int $currentPage The current page number.
     */
    private int $currentPage;

    private ?int $currentPageDefinedByUser = null;

    /**
     * Represents the number of pages in the pagination.
     *
     * @var int $numberOfPages The number of pages in the pagination.
     */
    private int $numberOfPages;

    /**
     * @var int $recordsPerPage The number of records to display per page.
     */
    private int $recordsPerPage = 10;

    /**
     * The total number of records in the pagination.
     */
    private int $totalRecordsCount = 0;

    /**
     * Constructor for the Pagination class.
     *
     * @param Db $db The database connection object.
     * @param PaginationOptions $paginationOptions The pagination options object.
     */
    public function __construct(Db $db, PaginationOptions $paginationOptions, View $view, int $recordsPerPage)
    {
        $this->db = $db;
        $this->paginationOptions = $paginationOptions;
        $this->view = $view;
        $this->recordsPerPage = $recordsPerPage;
    }

    /**
     * Selects data from the database.
     *
     * @param string $from The SQL FROM statement with optional joins.
     *                     Example: 'table1 INNER JOIN table2 ON table1.id = table2.id'.
     * @param string|array<string> $fields The columns to select. Can be a string or an array of strings.
     * @param array<int|string, mixed>|string $where The WHERE clause. Can be a string or an array of conditions.
     *                     If it's an array, the conditions will be joined with AND.
     * @param array<string, bool|int|string> $parameters An associative array of parameter names and values.
     * @param bool|string $debug false, true or silent.
     *              false:    the production mode.
     *              true:     The query is displayed.
     *              'silent': The query is registered then can be displayed using the getDebug() method.
     */
    public function select(
        string $from,
        $fields,
        $where = [],
        array $parameters = [],
        $debug = false
    ): self {
        // Get the total number of records
        try {
            $row = $this->db->selectCount($from, ['*' => 'rowsCount'], $where, $parameters, $debug);
            if (!$row || !is_object($row) || !property_exists($row, 'rowsCount')) {
                throw new PaginationException('Error getting the total number of records');
            }

            $this->totalRecordsCount = (int) $row->rowsCount;
        } catch (Exception $exception) {
            throw new PaginationException($exception->getMessage(), $exception->getCode(), $exception);
        }

        // Get the total number of pages
        $this->numberOfPages = (int) ceil($this->totalRecordsCount / $this->recordsPerPage);

        // Get the current page number
        $this->currentPage = $this->getCurrentPage();

        // Set the limit for the query
        $parameters['limit'] = (($this->currentPage - 1) * $this->recordsPerPage)  . ',' . $this->recordsPerPage;

        try {
            // Get the records for the current page
            if ($this->db->select($from, $fields, $where, $parameters, $debug)) {
                // Get the current number of records
                if ($rec = $this->db->numRows()) {
                    $this->currentNumberOfRecords = $rec;

                    return $this;
                }

                throw new PaginationException('Error getting the current number of records');
            }

            throw new PaginationException('Error getting the current page records');
        } catch (Exception $exception) {
            throw new PaginationException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * Fetches the next row from a result set and returns it according to the $fetch_parameters format
     *
     * @param int $fetch_parameters The PDO fetch style record options
     * @return mixed The next row or false if we reached the end
     */
    public function fetch(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        return $this->db->fetch($fetch_parameters);
    }

    /**
     * Fetches all rows from a result set and return them according to the $fetch_parameters format
     *
     * @param int $fetch_parameters The PDO fetch style record options
     * @return mixed The rows according to PDO fetch style or false if no record
     */
    public function fetchAll(int $fetch_parameters = PDO::FETCH_OBJ)
    {
        return $this->db->fetchAll($fetch_parameters);
    }

    /**
     * Build pagination
     *
     * @param string  $url         The base URL for the pagination links.
     *
     * @return string The HTML for the pagination links.
     */
    public function pagine(string $url): string
    {
        // reset the view
        $this->view->clear();

        // To build the links, check if $url already contains a ?
        $t   = $this->paginationOptions->rewriteTransition;
        $ext = $this->paginationOptions->rewriteExtension;
        $url = $this->removePreviousQuerystring($url);
        if (!$this->paginationOptions->rewriteLinks) {
            $t = strpos($url, "?") ? '&amp;' : '?';
        }

        // If there are records
        if ($this->totalRecordsCount !== 0) {
            $_SESSION['result_rs'] = $this->totalRecordsCount;

            $loopStart = max(1, $this->currentPage - $this->paginationOptions->navLength);
            $loopEnd = min($this->numberOfPages, $this->currentPage + $this->paginationOptions->navLength);

            // Building the list of pages
            if ($this->numberOfPages > 1) {
                for ($i = $loopStart; $i <= $loopEnd; ++$i) {
                    // If the page is the current page
                    if ($i == $this->currentPage) {
                        $this->view->add('<li class="page-item ' . $this->paginationOptions->activeClass . '"><a class="page-link" href="#">' . $i . '</a></li>');
                    } elseif ($i == 1) {
                        // If the page is the first page, the link is without query
                        if ($this->paginationOptions->rewriteLinks) {
                            $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $ext . '">' . $i . '</a></li>');
                        } else {
                            $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . '">' . $i . '</a></li>');
                        }
                    } elseif ($this->paginationOptions->rewriteLinks) {
                        // Otherwise, the link is with the query
                        $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . $i . $ext . '">' . $i . '</a></li>');
                    } else {
                        $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . '=' . $i . '">' . $i . '</a></li>');
                    }
                }

                if ($this->view->get() !== '' && $this->view->get() !== '0') {
                    $this->view->set('<li class="page-item ' . $this->paginationOptions->disabledClass . '"><a class="page-link" href="#">Page</a></li>' . $this->view->get());
                }

                if ($this->view->get() !== '' && $this->view->get() !== '0' && ($this->currentPage > 1)) {
                    if ($this->currentPage == 2) {
                        if ($this->paginationOptions->rewriteLinks) {
                            $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . $ext . '">' . $this->paginationOptions->previousMarkup . '</a></li>' . $this->view->get());
                        } else {
                            $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . '">' . $this->paginationOptions->previousMarkup . '</a></td>' . $this->view->get());
                        }
                    } elseif ($this->paginationOptions->rewriteLinks) {
                        //PREVIOUS
                        $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . ($this->currentPage - 1) . $ext . '">' . $this->paginationOptions->previousMarkup .
                            '</a></li>' . $this->view->get());
                    } else {
                        $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . '=' . ($this->currentPage - 1)  . '">' . $this->paginationOptions->previousMarkup .
                            '</a></li>' . $this->view->get());
                    }

                    // FIRST
                    if ($this->paginationOptions->rewriteLinks) {
                        $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . $ext . '">' . $this->paginationOptions->firstMarkup .
                            '</a></li>' . $this->view->get());
                    } else {
                        $this->view->set('<li class="page-item"><a class="page-link" href="' . $url . '">' . $this->paginationOptions->firstMarkup .
                            '</a></li>' . $this->view->get());
                    }
                }

                if ($this->view->get() !== '' && $this->view->get() !== '0' && ($this->currentPage < $this->numberOfPages)) { // NEXT, LAST
                    if ($this->paginationOptions->rewriteLinks) {
                        $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . ($this->currentPage + 1) . $ext . '">' . $this->paginationOptions->nextMarkup . '</a></li>'); // NEXT
                    } else {
                        $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . '=' . ($this->currentPage + 1)  . '">' . $this->paginationOptions->nextMarkup . '</a></li>');
                    }

                    if ($this->currentPage < $this->numberOfPages) { // LAST
                        if ($this->paginationOptions->rewriteLinks) {
                            $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . ($this->numberOfPages) . $ext . '">' . $this->paginationOptions->lastMarkup . '</a></li>');
                        } else {
                            $this->view->add('<li class="page-item"><a class="page-link" href="' . $url . $t . $this->paginationOptions->querystring . '=' . ($this->numberOfPages)  . '">' . $this->paginationOptions->lastMarkup . '</a></li>');
                        }
                    }
                }

                $start = $this->recordsPerPage * ($this->currentPage - 1) + 1;    // no. per page x current page.
                $end = $start + $this->currentNumberOfRecords - 1;
            } else {    // if there is only one page
                $start = 1;    // no. per page x current page.
                $end = $this->totalRecordsCount;
            }

            if ($this->totalRecordsCount > 0) {
                $this->view->set('<ul class="' . $this->paginationOptions->paginationClass . '">' . $this->view->get() . '</ul>');
            }

            $this->view->add('<div class="heading-elements pt-2 pr-3">');
            // CRUD admin i18n
            if (defined('PAGINATION_RESULTS') && defined('PAGINATION_OF') && defined('PAGINATION_TO')) {
                /** @disregard P1011 */
                $this->view->add('<p class="text-right text-semibold">' . PAGINATION_RESULTS . ' ' . $start . ' ' . PAGINATION_TO . ' ' . $end . ' ' . PAGINATION_OF . ' ' . $this->totalRecordsCount . '</p>');
            } else {
                $this->view->add('<p class="text-right text-semibold">results ' . $start . ' to ' . $end . ' of ' . $this->totalRecordsCount . '</p>');
            }

            $this->view->add('</div>');
        }

        return $this->view->get();
    }

    /**
     * Returns the current number of records.
     *
     * @return int The current number of records.
     */
    public function getCurrentNumberOfRecords(): int
    {
        return $this->currentNumberOfRecords;
    }

    /**
     * Retrieves the current page number.
     *
     * @return int The current page number.
     */
    public function getCurrentPage(): int
    {
        // If the current page number has been set by the user
        if ($this->currentPageDefinedByUser !== null) {
            return $this->currentPageDefinedByUser;
        }

        // Otherwise, get the current page number from the querystring
        $curPage = 1;

        if (isset($_GET[$this->paginationOptions->querystring])) {
            $curPage = (int) $_GET[$this->paginationOptions->querystring];
        }

        if ($curPage > $this->numberOfPages) {
            return $this->numberOfPages;
        }

        return $curPage;
    }

    /**
     * Get the database instance.
     *
     * @return Db The database instance.
     */
    public function getDb(): Db
    {
        return $this->db;
    }

    /**
     * Sets the current page number and overrides the querystring.
     *
     * @param int $currentPage The current page number.
     */
    public function setCurrentPage(int $currentPage): void
    {
        $this->currentPageDefinedByUser = $currentPage;
    }

    /**
     * Returns the number of pages.
     *
     * @return int The number of pages.
     */
    public function getNumberOfPages(): int
    {
        return $this->numberOfPages;
    }

    /**
     * Get the number of records to display per page.
     *
     * @return int The number of records per page.
     */
    public function getRecordsPerPage(): int
    {
        return $this->recordsPerPage;
    }

    /**
     * Sets the options for pagination.
     *
     * @param array<string, bool|string> $options An array of options for pagination.
     */
    public function setOptions(array $options): void
    {
        $this->paginationOptions->set($options);
    }

    /**
     * Sets the number of records to display per page.
     *
     * @param int $recordsPerPage The number of records to display per page.
     */
    public function setRecordsPerPage(int $recordsPerPage): self
    {
        $this->recordsPerPage = $recordsPerPage;
        return $this;
    }

    /**
     * Returns the total number of records in the dataset.
     *
     * @return int The total number of records.
     */
    public function getTotalRecordsCount(): int
    {
        return $this->totalRecordsCount;
    }

    /**
     * Removes any previous querystring parameters from the URL.
     *
     * @param string $url The URL to remove the querystring from
     * @return string The updated URL
     */
    private function removePreviousQuerystring(string $url): string
    {
        if ($this->paginationOptions->rewriteLinks) {
            $find = array('`' . $this->paginationOptions->rewriteTransition . $this->paginationOptions->querystring . '[0-9]+`', '`' . $this->paginationOptions->rewriteExtension . '`');
            $replace = array('', '');
        } else {
            $find = array('`\?' . $this->paginationOptions->querystring . '=([0-9]+)&(amp;)?`', '`\?' . $this->paginationOptions->querystring . '=([0-9]+)`', '`&(amp;)?' . $this->paginationOptions->querystring . '=([0-9]+)`');
            $replace = array('?', '', '');
        }

        $url = preg_replace($find, $replace, $url);

        return (string) $url;
    }
}
