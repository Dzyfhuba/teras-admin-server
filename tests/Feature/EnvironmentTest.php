<?php

use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\assertEquals;

test('Check Database Connection', function () {
    // database connection check
    $dbName = DB::connection()->getDatabaseName();

    $expectedDbName = env('DB_DATABASE');
    assertEquals($expectedDbName, $dbName);
});
