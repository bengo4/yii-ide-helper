<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Printers;

use PhpParser\Node;

interface PrinterInterface
{
    /**
     * @param string $code
     * @return Node[]
     */
    public function getAst(string $code): array;

    /**
     * @param Node[] $newStmts
     * @return string
     */
    public function print(array $newStmts): string;
}
