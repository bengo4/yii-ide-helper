<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Database;

use Exception;
use PDO;
use PDOException;
use PDOStatement;

class Database
{
    /**
     * @var PDO|null
     */
    private static $connection;

    /**
     * @var string[]
     */
    private const DEFAULT_SETTING = [
        'DATABASE_HOST' => '127.0.0.1',
        'DATABASE_PORT' => '3306',
        'DATABASE_USER' => 'root',
        'DATABASE_PASSWORD' => 'root',
    ];

    /**
     * @var string
     */
    private const NOT_NULL_FLAG = 'not_null';

    /**
     * @var boolean
     */
    private $emulateMode = false;

    /**
     * @param boolean $emulateMode ATTR_EMULATE_PREPARES (default false)
     */
    public function __construct(bool $emulateMode = false)
    {
        if (empty(self::$connection)) {
            $this->emulateMode = $emulateMode;
            self::$connection = $this->getConnection();
        }
    }

    /**
     * DBへのコネクションを取得します
     *
     * @return PDO
     */
    private function getConnection(): PDO
    {
        $databaseName = $_ENV['DATABASE_NAME'];
        $host = $_ENV['DATABASE_HOST'] ?? self::DEFAULT_SETTING['DATABASE_HOST'];
        $port = $_ENV['DATABASE_PORT'] ?? self::DEFAULT_SETTING['DATABASE_PORT'];

        $user = $_ENV['DATABASE_USER'] ?? self::DEFAULT_SETTING['DATABASE_USER'];
        $password = $_ENV['DATABASE_PASSWORD'] ?? self::DEFAULT_SETTING['DATABASE_PASSWORD'];

        $dsn = "mysql:dbname=${databaseName};host=${host};port=${port}";
        $option = [
            PDO::ATTR_EMULATE_PREPARES => $this->emulateMode,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET CHARACTER SET 'utf8'"
        ];
        try {
            $pdo = new PDO($dsn, $user, $password, $option);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return  $pdo;
        } catch (PDOException $e) {
            throw new Exception("接続失敗: " . $e->getMessage() . PHP_EOL);
        }
    }

    /**
     * sqlを実行します
     *
     * @param string $sql
     * @return PDOStatement|null
     */
    private function executeSql(string $sql): ?PDOStatement
    {
        try {
            $pdoStatement = self::$connection->query($sql);
            if (!$pdoStatement) {
                throw new PDOException('Execute failed.');
            }
            return $pdoStatement;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * PDOのカラム情報をPHPの型に変換します。
     *
     * @param string $tableName
     * @return array[]
     */
    public function getDBColumnData(string $tableName): array
    {
        $sql = "SELECT * FROM ${tableName} LIMIT 0";
        $pdoStatement = $this->executeSql($sql);

        if (empty($pdoStatement)) {
            return [];
        }

        $column = [];

        for ($i = 0; $i < $pdoStatement->columnCount(); $i++) {
            $meta = $pdoStatement->getColumnMeta($i);
            if ($meta) {
                $type = $this->emulateMode ? 'string' : $this->convertPhpType($meta['pdo_type']);

                $flag = join(',', $meta['flags']);
                if (empty($flag) || strpos($flag, self::NOT_NULL_FLAG) === false) {
                    $type .= '|null';
                }
                $column[] = [
                    'name' => $meta['name'],
                    'type' => $type
                ];
            }
        }

        return $column;
    }

    /**
     * pdo_typeで表現されるカラム型を、PHPの型に変換する
     *
     * @param int $pdoType
     * @return string
     */
    private function convertPhpType(int $pdoType): string
    {
        if ($pdoType === PDO::PARAM_STR || $pdoType === PDO::PARAM_STR_CHAR) {
            return 'string';
        }
        if ($pdoType === PDO::PARAM_INT) {
            return 'int';
        }
        if ($pdoType === PDO::PARAM_BOOL) {
            return 'bool';
        }
        return '';
    }
}
