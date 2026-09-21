<?php

class BridgeMysqliResult {
    public $num_rows = 0;
    private $rows = [];
    private $currentIndex = 0;

    public function __construct($pdoStmt) {
        $this->rows = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
        $this->num_rows = count($this->rows);
    }

    public function fetch_assoc() {
        if ($this->currentIndex < $this->num_rows) {
            return $this->rows[$this->currentIndex++];
        }
        return null;
    }

    public function fetch_array($mode = 3) { // 3 is MYSQLI_BOTH
        $row = $this->fetch_assoc();
        if (!$row) return null;
        if ($mode === 1) return $row; // MYSQLI_ASSOC
        if ($mode === 2) return array_values($row); // MYSQLI_NUM
        
        $both = $row;
        $num = array_values($row);
        foreach ($num as $k => $v) {
            $both[$k] = $v;
        }
        return $both;
    }

    public function fetch_all($mode = 1) { // MYSQLI_ASSOC
        return $this->rows;
    }
}

class BridgeMysqliStmt {
    private $stmt;
    private $conn;
    private $params = [];

    public function __construct($pdoStmt, $conn) {
        $this->stmt = $pdoStmt;
        $this->conn = $conn;
    }

    public function bind_param($types, &...$vars) {
        $this->params = [];
        foreach ($vars as &$v) {
            $this->params[] = &$v;
        }
        return true;
    }

    public function execute() {
        // Unref params for PDO execution
        $execParams = [];
        foreach ($this->params as $p) {
            $execParams[] = $p;
        }
        $res = $this->stmt->execute($execParams);
        if (!$res) {
            $err = $this->stmt->errorInfo();
            $this->conn->error = $err[2] ?? 'Execute error';
            return false;
        }
        $this->conn->insert_id = $this->conn->getPdo()->lastInsertId();
        return true;
    }

    public function get_result() {
        return new BridgeMysqliResult($this->stmt);
    }

    public function close() {
        return true;
    }
}

class BridgeMysqli {
    public $connect_error = null;
    public $error = '';
    public $errno = 0;
    public $insert_id = 0;
    private $pdo;

    public function __construct($dbPath, $sqlDumpPath) {
        if (!file_exists($dbPath)) {
            self::initDatabase($dbPath, $sqlDumpPath);
        }
        try {
            $this->pdo = new PDO('sqlite:' . $dbPath);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        } catch (Exception $e) {
            $this->connect_error = $e->getMessage();
        }
    }

    public function getPdo() {
        return $this->pdo;
    }

    public static function initDatabase($dbPath, $sqlDumpPath) {
        if (!file_exists($sqlDumpPath)) return;

        $pdo = new PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

        $content = file_get_contents($sqlDumpPath);
        $content = preg_replace('/\/\*!.*?\*\/\s*;/s', '', $content);
        $content = preg_replace('/\/\*!.*?\*\//s', '', $content);
        $content = preg_replace('/LOCK TABLES `[^`]+` (WRITE|READ);/i', '', $content);
        $content = preg_replace('/UNLOCK TABLES;/i', '', $content);
        $content = str_replace("\\'", "''", $content);

        $content = preg_replace_callback('/CREATE TABLE `([^`]+)` \((.*?)\) ENGINE=[^;]+;/s', function($matches) {
            $table = $matches[1];
            $body = $matches[2];

            if (preg_match('/PRIMARY KEY \(`([^`]+)`\)/i', $body, $pk)) {
                $pkCol = $pk[1];
                $body = preg_replace('/,\s*PRIMARY KEY \(`' . preg_quote($pkCol, '/') . '`\)/i', '', $body);
                $body = preg_replace('/`' . preg_quote($pkCol, '/') . '`\s+int\(\d+\)\s+NOT NULL\s+AUTO_INCREMENT/i', '`' . $pkCol . '` INTEGER PRIMARY KEY AUTOINCREMENT', $body);
                $body = preg_replace('/`' . preg_quote($pkCol, '/') . '`\s+int\(\d+\)\s+AUTO_INCREMENT/i', '`' . $pkCol . '` INTEGER PRIMARY KEY AUTOINCREMENT', $body);
            }
            $body = preg_replace('/`([^`]+)`\s+int\(\d+\)\s+(NOT NULL\s+)?AUTO_INCREMENT/i', '`$1` INTEGER PRIMARY KEY AUTOINCREMENT', $body);

            $body = preg_replace('/UNIQUE KEY `[^`]+` \(`([^`]+)`\)/i', 'UNIQUE(`$1`)', $body);
            $body = preg_replace('/,\s*KEY `[^`]+` \(`([^`]+)`\)/i', '', $body);

            $body = preg_replace('/\bint\(\d+\)/i', 'INTEGER', $body);
            $body = preg_replace('/\bvarchar\(\d+\)/i', 'TEXT', $body);
            $body = preg_replace('/\btimestamp NOT NULL DEFAULT current_timestamp\(\)/i', 'TEXT DEFAULT CURRENT_TIMESTAMP', $body);
            $body = preg_replace('/\btimestamp NOT NULL DEFAULT CURRENT_TIMESTAMP/i', 'TEXT DEFAULT CURRENT_TIMESTAMP', $body);
            $body = preg_replace('/\bdatetime\b/i', 'TEXT', $body);
            $body = preg_replace('/\blongtext\b/i', 'TEXT', $body);
            $body = preg_replace('/\btext\b/i', 'TEXT', $body);
            $body = preg_replace('/\bdate\b/i', 'TEXT', $body);

            return "CREATE TABLE IF NOT EXISTS `$table` ($body);";
        }, $content);

        $queries = preg_split('/;\s*[\r\n]+/', $content);
        foreach ($queries as $q) {
            $q = trim($q);
            if (empty($q) || substr($q, 0, 2) === '--') continue;
            $pdo->exec($q);
        }
    }

    public function query($sql) {
        $this->error = '';
        $this->errno = 0;

        $sql = preg_replace('/NOW\(\)/i', "datetime('now')", $sql);

        if (preg_match('/LIMIT\s+(\d+)\s*,\s*(\d+)/i', $sql, $m)) {
            $sql = preg_replace('/LIMIT\s+\d+\s*,\s*\d+/i', "LIMIT {$m[2]} OFFSET {$m[1]}", $sql);
        }

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            $err = $this->pdo->errorInfo();
            $this->error = $err[2] ?? 'Query error';
            $this->errno = $err[1] ?? 1;
            return false;
        }

        $this->insert_id = $this->pdo->lastInsertId();

        if (preg_match('/^\s*(SELECT|PRAGMA|EXPLAIN|SHOW)/i', $sql)) {
            return new BridgeMysqliResult($stmt);
        }

        return true;
    }

    public function prepare($sql) {
        $sql = preg_replace('/NOW\(\)/i', "datetime('now')", $sql);
        $stmt = $this->pdo->prepare($sql);
        return new BridgeMysqliStmt($stmt, $this);
    }

    public function real_escape_string($str) {
        return str_replace("'", "''", $str);
    }

    public function escape_string($str) {
        return $this->real_escape_string($str);
    }

    public function close() {
        return true;
    }
}
