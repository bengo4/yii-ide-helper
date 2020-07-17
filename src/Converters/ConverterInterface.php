<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters;

interface ConverterInterface
{
    /**
     * ファイルを変換する
     *
     * @param string $filePath
     * @param string $code
     * @return string
     */
    public function convert(string $filePath, string $code): string;
}
