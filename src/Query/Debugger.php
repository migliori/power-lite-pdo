<?php

namespace Migliori\PowerLitePdo\Query;

use PDOStatement;
use Migliori\PowerLitePdo\View\View;

class Debugger
{
    /**
     * Represents a debugger for the Query package.
     *
     * @var View $view The view object used for debugging.
     */
    private View $view;

    /**
     * Sets the debugging information for a query.
     *
     */
    public function __construct(View $view)
    {
        $this->view = $view;
    }

    /**
     * Dumps the information related to a database query for debugging purposes.
     *
     * @param string $queryType The type of the query (e.g. SELECT, INSERT, UPDATE, DELETE).
     * @param array<int|string, mixed>|null $placeholders The array of placeholders used in the query.
     * @param ?PDOStatement $pdoStatement The PDOStatement object representing the query.
     * @param string $interpolatedSql The interpolated SQL query.
     * @param int|float|null $time The execution time of the query.
     * @param ?string $error The error message, if any.
     */
    public function dump(string $queryType, ?array $placeholders, ?PDOStatement $pdoStatement, string $interpolatedSql, $time, ?string $error = null): void
    {
        $random_colors = ['coral', 'crimson', 'dodgerblue', 'darkcyan', 'darkgoldenrod', 'deeppink', 'forestgreen', 'goldenrod', 'mediumpurple', 'mediumseagreen'];
        shuffle($random_colors);

        // Format the queryType
        $queryType = '<span style="color:' . $random_colors[0] . '">' . strtoupper($queryType) . '</span>';

        $this->view->add('<div class="db-dump-debug" style="margin:1rem;padding:1rem;border:1px solid #ccc;">');

        // if INSERT|UPDATE|DELETE
        if (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'])) {
            $this->view->add('<p style="color:white;background-color:#C22404;padding:.25rem .5rem;"><strong>DEBUG mode enabled.</strong> The INSERT, UPDATE and DELETE queries are only simulated.</p>');
        }

        // If there was an error specified
        if (!is_null($error)) {
            // Show the error information
            $this->view->add("\n<br>\n--<strong>DEBUG " . $queryType . " ERROR</strong>--\n<pre><code>");
            $this->view->add($error);
        }

        // If the number of seconds is specified...
        if (is_numeric($time)) {
            // Show how long it took
            $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " TIMER</strong>--\n<pre><code>");
            $this->view->add(number_format($time, 6) . ' ms');
        }

        // Output the interpolated (the 'real') SQL
        $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " SQL</strong>--\n<pre><code>");
        $this->view->add(print_r($interpolatedSql, true));

        // If there were placeholders passed in...
        if (!is_null($placeholders)) {
            // Show the placeholders
            $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " PARAMS</strong>--\n<pre><code>");
            $this->view->add(print_r($placeholders, true));
        }

        // If no query was executed...
        if (is_null($pdoStatement)) {
            $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " DUMP</strong>--\n<pre><code>");
            $this->view->add('No query was executed.');
            $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " END</strong>--\n</div>\n");
            return;
        }

        // Show the query dump
        ob_start();
        $pdoStatement->debugDumpParams();
        $qp = ob_get_contents();
        ob_end_clean();
        $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " DUMP</strong>--\n<pre><code>");
        $this->view->add(print_r($qp, true));

        // If records were returned...
        if (in_array($queryType, ['INSERT', 'UPDATE', 'DELETE'])) {
            // Show the count
            $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " AFFECTED ROWS</strong>--\n<pre><code>");
            $this->view->add(print_r($pdoStatement->rowCount(), true));
        }

        // End the debug output
        $this->view->add("</code></pre>\n--<strong>DEBUG " . $queryType . " END</strong>--\n</div>\n");
    }

    /**
     * Get the view associated with the Debugger class.
     *
     * @return View The view associated with the Debugger class.
     */
    public function getView(): View
    {
        return $this->view;
    }

    /**
     * Renders the debugging information.
     */
    public function render(): void
    {
        $this->view->render();
    }
}
