<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\FileSystem;

use RuntimeException;

class FileRecursiveSearcher
{
    /**
     * @var string
     */
    private const PHP_PATTERN = '/.*\.php/';

    /**
     * 再帰的にファイルのパスを取得します
     *
     * @param string $dir
     * @param string $fileType デフォルトphpファイルのみ
     * @return string[]
     * @throws RuntimeException 対象のディレクトリが存在しないとき
     */
    public function getFileSystemPath(string $dir, string $fileType = self::PHP_PATTERN): array
    {
        if (is_file($dir)) {
            return [$dir];
        }

        if (!is_dir($dir) || !($list = scandir($dir))) {
            throw new RuntimeException("Not found such a directory. path: ${dir}");
        }

        $results = [];

        foreach ($list as $record) {
            if (in_array($record, [".", ".."])) {
                continue;
            }
            $path = rtrim($dir, "/") . "/" . $record;
            if (is_file($path) && preg_match($fileType, $path)) {
                $results[] = $path;
            }
            if (is_dir($path)) {
                $results = array_merge($results, $this->getFileSystemPath($path));
            }
        }

        return $results;
    }
}
