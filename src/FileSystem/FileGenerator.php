<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\FileSystem;

use Bengo4\YiiIdeHelper\FileSystem\FileRecursiveSearcher;
use Bengo4\YiiIdeHelper\Converters\ConverterInterface;

class FileGenerator
{
    /**
     * @var ConverterInterface
     */
    private $converter;

    /**
     * @param ConverterInterface $converter
     */
    public function __construct(ConverterInterface $converter)
    {
        $this->converter = $converter;
    }

    /**
     * 再帰的にファイルを取得し、converterを元に変換します。
     *
     * @param string $dirName
     * @return void
     */
    public function generate(string $dirName): void
    {
        $filePaths = (new FileRecursiveSearcher())->getFileSystemPath($dirName);

        foreach ($filePaths as $filePath) {
            $code = file_get_contents($filePath);
            if (!$code) {
                continue;
            }
            $newCode = $this->converter->convert($filePath, $code);

            if ($newCode) {
                file_put_contents($filePath, $newCode);
            }
        }
    }
}
