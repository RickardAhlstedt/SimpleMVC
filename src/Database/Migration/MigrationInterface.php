<?php

namespace SimpleMVC\Database\Migration;

use PDO;

interface MigrationInterface
{
    public function up(PDO $connection): void;
    public function down(PDO $connection): void;
}
