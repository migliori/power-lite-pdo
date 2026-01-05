<?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.3/cosmo/bootstrap.min.css" integrity="sha512-PU+mnI7iaSDt/G/adHVcQOX2I+K3bQ27kwHJQ1rPq5iqQvHuHSdJOUU/TmPcUsyUGrfAxK+Z4rnx/SL+qCmBNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Prism.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/themes/prism-tomorrow.min.css" integrity="sha512-kSwGoyIkfz4+hMo5jkJngSByil9jxJPKbweYec/UgS+S1EgE45qm4Gea7Ks2oxQ7qiYyyZRn66A9df2lMtjIsw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Db examples - PowerLite PDO</title>
</head>

<body>
    <h1 class="text-center mb-4">Db examples - PowerLite PDO</h1>

    <!-- ============================================
    =                   Example 1                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 1<br><small class="text-black-50">Select all records from a table then loop through them and display the results</small></h2>

        <?php

        $from = 'users'; // The table name
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $db->select($from, $fields, $where);

        $records = [];
        while ($record = $db->fetch()) {
            $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$db->select($from, $fields, $where);

$records = [];
while ($record = $db->fetch()) {
    $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
}
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-1" aria-expanded="false" aria-controls="phpOutput-1">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-1">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records); ?>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 2                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 2<br><small class="text-black-50">Select all records from a table, fetch all the results then convert them to an indexed and associative array</small></h2>

        <?php
        $from = 'users'; // The table name
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $db->select($from, $fields, $where);

        // fetch all rows from the result set and return them as associative arrays.
        $result = $db->fetchAll(PDO::FETCH_ASSOC);

        // convert the result to an indexed array. E.g.: ["name,", "name2", ...]
        $output1 = $db->convertToSimpleArray($result, 'name');

        // convert the result to an associative array. E.g.: ["name" => "email", ...]
        // note that the arguments order is 'value', 'key', because the key is optional
        $output2 = $db->convertToSimpleArray($result, 'email', 'name');
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$db->select($from, $fields, $where);

// fetch all rows from the result set and return them as associative arrays.
$result = $db->fetchAll(PDO::FETCH_ASSOC);

// convert the result to an indexed array. E.g.: ["name,", "name2", ...]
$output1 = $db->convertToSimpleArray($result, 'name');

// convert the result to an associative array. E.g.: ["name" => "email", ...]
// note that the arguments order is 'value', 'key', because the key is optional
$output2 = $db->convertToSimpleArray($result, 'email', 'name');
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-2" aria-expanded="false" aria-controls="phpOutput-2">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-2">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <pre class="mb-5"><?php echo '// $output1' . "\n" . print_r($output1, true); ?></pre>
                <pre><?php echo '// $output2' . "\n" . print_r($output2, true); ?></pre>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 3                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 3<br><small class="text-black-50">Using transactions to ensure data integrity</small></h2>

        <?php
        // Example: Transfer points from one user to another using a transaction
        $fromUserId = 3;
        $toUserId = 5;
        $points = 10;

        $success = false;
        $message = '';

        try {
            // Begin the transaction
            $db->transactionBegin();

            // Deduct points from the first user
            $values = ['status' => 'inactive'];
            $where = ['id' => $fromUserId];
            $db->update('users', $values, $where);

            // Add points to the second user
            $values = ['status' => 'active'];
            $where = ['id' => $toUserId];
            $db->update('users', $values, $where);

            // Commit the transaction if everything is successful
            $db->transactionCommit();

            $success = true;
            $message = "Transaction completed successfully! User $fromUserId status changed to inactive, User $toUserId status changed to active.";
        } catch (Exception $e) {
            // Rollback the transaction if something went wrong
            $db->transactionRollback();

            $message = "Transaction failed and rolled back: " . $e->getMessage();
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

// Example: Transfer points from one user to another using a transaction
$fromUserId = 3;
$toUserId = 5;
$points = 10;

$success = false;
$message = '';

try {
    // Begin the transaction
    $db->transactionBegin();

    // Deduct points from the first user
    $values = ['status' => 'inactive'];
    $where = ['id' => $fromUserId];
    $db->update('users', $values, $where);

    // Add points to the second user
    $values = ['status' => 'active'];
    $where = ['id' => $toUserId];
    $db->update('users', $values, $where);

    // Commit the transaction if everything is successful
    $db->transactionCommit();

    $success = true;
    $message = "Transaction completed successfully! User $fromUserId status changed to inactive, User $toUserId status changed to active.";
} catch (Exception $e) {
    // Rollback the transaction if something went wrong
    $db->transactionRollback();

    $message = "Transaction failed and rolled back: " . $e->getMessage();
}
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-3" aria-expanded="false" aria-controls="phpOutput-3">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-3">
            <div class="card card-body <?php echo $success ? 'bg-success-subtle' : 'bg-danger-subtle'; ?>">
                <?php echo htmlspecialchars($message); ?>
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
<?php
$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootswatch/5.3.3/cosmo/bootstrap.min.css" integrity="sha512-PU+mnI7iaSDt/G/adHVcQOX2I+K3bQ27kwHJQ1rPq5iqQvHuHSdJOUU/TmPcUsyUGrfAxK+Z4rnx/SL+qCmBNQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Prism.js CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/9000.0.1/themes/prism-tomorrow.min.css" integrity="sha512-kSwGoyIkfz4+hMo5jkJngSByil9jxJPKbweYec/UgS+S1EgE45qm4Gea7Ks2oxQ7qiYyyZRn66A9df2lMtjIsw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <title>Db examples - PowerLite PDO</title>
</head>

<body>
    <h1 class="text-center mb-4">Db examples - PowerLite PDO</h1>

    <!-- ============================================
    =                   Example 1                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 1<br><small class="text-black-50">Select all records from a table then loop through them and display the results</small></h2>

        <?php

        $from = 'users'; // The table name
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $db->select($from, $fields, $where);

        $records = [];
        while ($record = $db->fetch()) {
            $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$db->select($from, $fields, $where);

$records = [];
while ($record = $db->fetch()) {
    $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
}
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-1" aria-expanded="false" aria-controls="phpOutput-1">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-1">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records); ?>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 2                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 2<br><small class="text-black-50">Select all records from a table, fetch all the results then convert them to an indexed and associative array</small></h2>

        <?php
        $from = 'users'; // The table name
        $fields = ['id', 'name', 'email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $db->select($from, $fields, $where);

        // fetch all rows from the result set and return them as associative arrays.
        $result = $db->fetchAll(PDO::FETCH_ASSOC);

        // convert the result to an indexed array. E.g.: ["name,", "name2", ...]
        $output1 = $db->convertToSimpleArray($result, 'name');

        // convert the result to an associative array. E.g.: ["name" => "email", ...]
        // note that the arguments order is 'value', 'key', because the key is optional
        $output2 = $db->convertToSimpleArray($result, 'email', 'name');
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$fields = ['id', 'name', 'email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$db->select($from, $fields, $where);

// fetch all rows from the result set and return them as associative arrays.
$result = $db->fetchAll(PDO::FETCH_ASSOC);

// convert the result to an indexed array. E.g.: ["name,", "name2", ...]
$output1 = $db->convertToSimpleArray($result, 'name');

// convert the result to an associative array. E.g.: ["name" => "email", ...]
// note that the arguments order is 'value', 'key', because the key is optional
$output2 = $db->convertToSimpleArray($result, 'email', 'name');
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-2" aria-expanded="false" aria-controls="phpOutput-2">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-2">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <pre class="mb-5"><?php echo '// $output1' . "\n" . print_r($output1, true); ?></pre>
                <pre><?php echo '// $output2' . "\n" . print_r($output2, true); ?></pre>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 3                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 3<br><small class="text-black-50">Using <code class="php">selectValue</code> to select a single value from a table</small></h2>

        <?php
        $from = 'users'; // The table name
        $field = ['email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $userEmail = $db->selectValue($from, $field, $where);
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$field = ['email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$userEmail = $db->selectValue($from, $field, $where);
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-3" aria-expanded="false" aria-controls="phpOutput-3">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-3">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <pre class="mb-5"><?php echo '// $userEmail' . "\n" . print_r($userEmail, true); ?></pre>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 4                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 4<br><small class="text-black-50">Using <code class="php">selectCount</code> to count records from a query</small></h2>

        <?php
        /* public selectCount(string $from[, string|array<string|int, string> $fields = ['*' => 'rowsCount'] ][, array<int|string, mixed>|string $where = [] ][, array<string, bool|int|string> $parameters = [] ][, bool|string $debug = false ]) : mixed */
        $from = 'users'; // The table name
        $fields = ['*' => 'rowsCount']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $total = $db->selectCount($from, $fields, $where);
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$fields = ['*' => 'rowsCount']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$total = $db->selectCount($from, $fields, $where);
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-4" aria-expanded="false" aria-controls="phpOutput-4">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-4">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <pre class="mb-5"><?php echo '// $total' . "\n" . print_r($total, true); ?></pre>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 5                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 5<br><small class="text-black-50">Using <code class="php">numRows</code> to count the number of rows returned by a query</small></h2>
        <?php
        $from = 'users'; // The table name
        $fields = ['email']; // The columns you want to select
        $where = ['status' => 'active']; // The conditions for the WHERE clause

        $db->select($from, $fields, $where);

        $totalRows = $db->numRows();
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

$from = 'users'; // The table name
$field = ['email']; // The columns you want to select
$where = ['status' => 'active']; // The conditions for the WHERE clause

$db->select($from, $fields, $where);

$totalRows = $db->numRows();
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-5" aria-expanded="false" aria-controls="phpOutput-5">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-5">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <pre class="mb-5"><?php echo '// $totalRows' . "\n" . print_r($totalRows, true); ?></pre>
            </div>
        </div>
    </article>

    <!-- ============================================
    =                   Example 6                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 6<br><small class="text-black-50">Using transactions to ensure data integrity</small></h2>
        <?php
        // Example: Transfer points from one user to another using a transaction
        $fromUserId = 3;
        $toUserId = 5;
        $points = 10;

        $success = false;
        $message = '';

        try {
            // Begin the transaction
            $db->transactionBegin();

            // Deduct points from the first user
            $values = ['status' => 'inactive'];
            $where = ['id' => $fromUserId];
            $db->update('users', $values, $where);

            // Add points to the second user
            $values = ['status' => 'active'];
            $where = ['id' => $toUserId];
            $db->update('users', $values, $where);

            // Commit the transaction if everything is successful
            $db->transactionCommit();

            $success = true;
            $message = "Transaction completed successfully! User $fromUserId status changed to inactive, User $toUserId status changed to active.";
        } catch (Exception $e) {
            // Rollback the transaction if something went wrong
            $db->transactionRollback();

            $message = "Transaction failed and rolled back: " . $e->getMessage();
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$db = $container->get(Db::class);

// Example: Transfer points from one user to another using a transaction
$fromUserId = 3;
$toUserId = 5;
$points = 10;

$success = false;
$message = '';

try {
    // Begin the transaction
    $db->transactionBegin();

    // Deduct points from the first user
    $values = ['status' => 'inactive'];
    $where = ['id' => $fromUserId];
    $db->update('users', $values, $where);

    // Add points to the second user
    $values = ['status' => 'active'];
    $where = ['id' => $toUserId];
    $db->update('users', $values, $where);

    // Commit the transaction if everything is successful
    $db->transactionCommit();

    $success = true;
    $message = "Transaction completed successfully! User $fromUserId status changed to inactive, User $toUserId status changed to active.";
} catch (Exception $e) {
    // Rollback the transaction if something went wrong
    $db->transactionRollback();

    $message = "Transaction failed and rolled back: " . $e->getMessage();
}
</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput-6" aria-expanded="false" aria-controls="phpOutput-6">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput-6">
            <div class="card card-body <?php echo $success ? 'bg-success-subtle' : 'bg-danger-subtle'; ?>">
                <?php echo htmlspecialchars($message); ?>
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
