<?php

namespace App\Interface\Controller;

use PDO;

class dbHealthController
{
    public function __construct(private PDO $db) {}

    public function check()
    {
        $now = $this->db->query("SELECT now()")->fetch();
        echo json_encode([
            'status' => 'ok',
            'db_time' => $now['now'] ?? null
        ]);
    }
}
