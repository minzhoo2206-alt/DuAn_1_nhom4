<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class Transaction
{
    public function __construct(
        private PDO $db
    ) {
    }

    public function run(callable $callback): mixed
    {
        $this->db->beginTransaction();

        try {
            $result = $callback($this->db);

            $this->db->commit();

            return $result;

        } catch (Throwable $exception) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $exception;
        }
    }
}
