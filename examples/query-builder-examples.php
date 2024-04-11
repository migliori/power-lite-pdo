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

    <title>Query Builder examples - PowerLite PDO</title>
</head>

<body>
    <h1 class="text-center mb-4">Query Builder examples - PowerLite PDO</h1>

    <!-- ============================================
    =                   Example 1                   =
    ============================================= -->

    <article class="container py-5">
        <h2 class="mb-3">Example 1</h2>

        <?php

        use Migliori\PowerLitePdo\Query\QueryBuilder;

        $container = require_once __DIR__ . '/../src/bootstrap.php';
        $queryBuilder = $container->get(QueryBuilder::class);

        $queryBuilder->select(['id', 'name', 'email'])->from('users')->where(['status' => 'active'])->execute();

        $records = [];
        while ($record = $queryBuilder->fetch()) {
            $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
        }
        ?>

        <pre><code class="language-php">&lt;?php

use Migliori\PowerLitePdo\Db;

$container = require_once __DIR__ . '/../src/bootstrap.php';

$queryBuilder = $container->get(QueryBuilder::class);

$queryBuilder->select(['id', 'name', 'email'])->from('users')->where(['status' => 'active'])->execute();

$records = [];
while ($record = $db->fetch()) {
    $records[] = $record->id . ', ' . $record->name . ', ' . $record->email;
}</code></pre>

        <button class="btn btn-primary dropdown-toggle mt-3" type="button" data-bs-toggle="collapse" data-bs-target="#phpOutput" aria-expanded="false" aria-controls="phpOutput">
            Show / Hide the result
        </button>

        <div class="collapse mt-3" id="phpOutput">
            <div class="card card-body overflow-auto" style="max-height: 400px;">
                <?php echo implode('<br>', $records); ?>
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
