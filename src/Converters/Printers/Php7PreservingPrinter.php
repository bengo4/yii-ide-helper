<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Printers;

use PhpParser\Lexer\Emulative;
use PhpParser\Node\Stmt;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser\Php7;
use PhpParser\PrettyPrinter\Standard;

final class Php7PreservingPrinter implements PrinterInterface
{
    /**
     * @var Stmt[]
     */
    private $oldStmts;

    /**
     * @var Stmt[]
     */
    private $oldTokens;

    /**
     * コードを構文木に変換し、コピーして変数に格納します
     *
     * @inheritDoc
     */
    public function getAst(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine',
                'endLine',
                'startTokenPos',
                'endTokenPos',
            ],
        ]);

        $parser = new Php7($lexer);
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());

        $this->oldStmts = $parser->parse($code);
        $this->oldTokens = $lexer->getTokens();
        return $traverser->traverse($this->oldStmts);
    }

    /**
     * 変換前のファイルと比較して、変更分のみを調整して出力します。
     *
     * @inheritDoc
     */
    public function print(array $newStmts): string
    {
        assert_options(ASSERT_ACTIVE, 0);

        $code = (new Standard)->printFormatPreserving(
            $newStmts,
            $this->oldStmts,
            $this->oldTokens
        );
        assert_options(ASSERT_ACTIVE, 1);

        return $this->addPhpEol($code);
    }

    /**
     * ファイルの末尾に空行を追加します
     *
     * @param string $code
     * @return string $code
     */
    private function addPhpEol(string $code): string
    {
        if (substr($code, -1) !== "\n") {
            $code .= PHP_EOL;
        }

        return $code;
    }
}
