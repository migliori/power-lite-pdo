<?php

declare(strict_types=1);

use Migliori\PowerLitePdo\Pagination;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$pagination = $container->get(Pagination::class);
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.3/cosmo/bootstrap.min.css" integrity="sha512-PU+mnI7iaSDt/G/adHVcQOX2I+K3bQ27kwHJQ1rPq5iqQvHuHSdJOUU/TmPcUsyUGrfAxK+Z4rnx/SL+qCmBNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Fontawesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Prism.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/themes/prism-tomorrow.min.css" integrity="sha512-kSwGoyIkfz4+hMo5jkJngSByil9jxJPKbweYec/UgS+S1EgE45qm4Gea7Ks2oxQ7qiYyyZRn66A9df2lMtjIsw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Pagination examples - PowerLite PDO</title>
</head>

<body>
    <h1 class="text-center mb-4">Pagination examples - PowerLite PDO</h1>

    <!-- ============================================
    =  Example 1 - Pagination without url rewriting =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Pagination without url rewriting</h2>

        <?php
        $from = 'users'; // The SELECT FROM clause
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $pagination->select($from, $fields, $where);

        $records = [];
        while ($record = $pagination->fetch()) {
            $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$pagination = $container->get(Pagination::class);

$from = 'users'; // The SELECT FROM clause
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$pagination->select($from, $fields, $where);

$records = [];
while ($record = $db->fetch()) {
    $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
}

$url = '/examples/pagination-examples.php'; // The URL for the pagination links
echo $pagination->pagine($url);</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#example-1" aria-expanded="false" aria-controls="example-1">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="example-1">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records); ?>
                <p>&nbsp;</p>
                <?php
                $url = '/examples/pagination-examples.php'; // The URL for the pagination links
                echo $pagination->pagine($url);
                ?>
            </div>
        </div>
    </article>

    <!-- ============================================
    =   Example 2 - Pagination with url rewriting   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Pagination with url rewriting</h2>

        <?php
        // Set the pagination options for URL rewriting
        $pagination->setOptions([
            'querystring' => '',
            'rewriteLinks' => true,
            'rewriteTransition' => '-',
            'rewriteExtension' => '.html'
        ]);

        $from = 'users'; // The SELECT FROM clause
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $pagination->select($from, $fields, $where);

        $records2 = [];
        while ($record = $pagination->fetch()) {
            $records2[] = $record->id . ', ' . $record->name . ', ' . $record->email;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$pagination = $container->get(Pagination::class);

// Set the pagination options for URL rewriting
$pagination->setOptions([
    'querystring' => '',
    'rewriteLinks' => true,
    'rewriteTransition' => '-',
    'rewriteExtension' => '.html'
]);

$from = 'users'; // The SELECT FROM clause
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$pagination->select($from, $fields, $where);

$records2 = [];
while ($record = $db->fetch()) {
    $records2[] = $record->id . ', ' . $record->name . ', ' . $record->email;
}

$url = '/examples/pagination-examples'; // The URL for the pagination links
echo $pagination->pagine($url);</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#example-2" aria-expanded="false" aria-controls="example-2">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="example-2">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records2); ?>
                <p>&nbsp;</p>
                <?php
                $url = '/examples/pagination-examples'; // The URL for the pagination links
                echo $pagination->pagine($url);
                ?>
            </div>
        </div>
    </article>

    <!-- ===============================================================
    =   Example 3 - Set records per page, Select with JOIN and LIMIT   =
    ================================================================ -->

    <article class="container py-5">
        <h2 class="mb-3">Set records per page, Select with JOIN and LIMIT</h2>

        <?php
        // Revert to the pagination default options without URL rewriting
        // set the querystring to 'page' to avoid conflicts with the querystring 'p' used in the previous examples
        $pagination->setOptions([
            'querystring' => 'page',
            'rewriteLinks' => false,
            'rewriteTransition' => '&',
            'rewriteExtension' => ''
        ]);

        $from = 'orders INNER JOIN customers ON orders.customers_id = customers.id'; // The SELECT FROM clause
        $fields = ['orders.order_date', 'orders.reference', 'customers.first_name', 'customers.last_name']; // The columns you want to select
        $where = []; // The conditions for the WHERE clause
        $parameters = [
            'limit' => 15,
            'orderBy' => 'orders.order_date DESC'
        ]; // The parameters for the LIMIT clause

        $pagination->setRecordsPerPage(3)->select($from, $fields, $where, $parameters);

        $records3 = [];
        while ($record = $pagination->fetch()) {
            $records3[] = $record->order_date . ', ' . $record->reference . ' :: ' . $record->last_name . ' ' . $record->first_name;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$pagination = $container->get(Pagination::class);

// Revert to the pagination default options without URL rewriting
// set the querystring to 'page' to avoid conflicts with the querystring 'p' used in the previous examples
$pagination->setOptions([
    'querystring' => 'page',
    'rewriteLinks' => false,
    'rewriteTransition' => '&',
    'rewriteExtension' => ''
]);

$from = 'orders INNER JOIN customers ON orders.customers_id = customers.id'; // The SELECT FROM clause
$fields = ['orders.order_date', 'orders.reference', 'customers.first_name', 'customers.last_name']; // The columns you want to select
$where = []; // The conditions for the WHERE clause
$parameters = [
    'limit' => 15
    'orderBy' => 'orders.order_date DESC'
]; // The parameters for the LIMIT clause

$pagination->setRecordsPerPage(3)->select($from, $fields, $where, $parameters);

$records3 = [];
while ($record = $pagination->fetch()) {
    $records3[] = $record->order_date . ', ' . $record->reference . ' :: ' . $record->last_name . ' ' . $record->first_name;
}</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#example-3" aria-expanded="false" aria-controls="example-3">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="example-3">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records3); ?>
                <p>&nbsp;</p>
                <?php
                $url = '/examples/pagination-examples.php'; // The URL for the pagination links
                echo $pagination->pagine($url);
                ?>
            </div>
        </div>
    </article>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Prism.js JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/components/prism-php.min.js" integrity="sha512-6UGCfZS8v5U+CkSBhDy+0cA3hHrcEIlIy2++BAjetYt+pnKGWGzcn+Pynk41SIiyV2Oj0IBOLqWCKS3Oa+v/Aw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
</body>

</html>
