<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc;

use PhpParser\Comment\Doc;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\PhpDocBuilder;

class ClassPhpDocBuilder extends PhpDocBuilder
{
    /**
     * @var int
     */
    private $addLineCount = 0;

    /**
     * @var Property[] $propertyList
     */
    private $propertyList;

    /**
     * @var Method[] $methodList
     */
    private $methodList;

    /**
     * @var string
     */
    private const PROPERTY_PHP_DOC = '@property';

    /**
     * @var string
     */
    private const METHOD_PHP_DOC = '@method';

    /**
     * @param Property[] $propertyList
     * @return void
     */
    public function setPropertyList(array $propertyList): void
    {
        $this->propertyList = $propertyList;
    }

    /**
     * @param Method[] $methodList
     * @return void
     */
    public function setMethodList(array $methodList): void
    {
        $this->methodList = $methodList;
    }

    private function getAndFormatStartPoint(string $type): int
    {
        $addPosition = count($this->comments) - 1;

        for ($i = 0; $i < count($this->comments); $i++) {
            if (strpos($this->comments[$i], $type)) {
                $addPosition = $i + 1;
            }
        }
        return $addPosition;
    }

    /**
     * propertyのPHPDocを付与します
     */
    public function addPropertyDoc(): void
    {
        if (empty($this->propertyList)) {
            return;
        }

        $addPosition = $this->getAndFormatStartPoint(self::PROPERTY_PHP_DOC);
        $baseComment = join(PHP_EOL, $this->comments);

        foreach ($this->propertyList as $property) {
            if (! preg_match('/\$' . $property->name . '( |'. PHP_EOL . ').*/', $baseComment)) {
                $this->addComment(
                    ' * ' . self::PROPERTY_PHP_DOC . " {$property->type} \${$property->name}",
                    $addPosition
                );
                $addPosition =+ 1;
            }
        }
    }

    /**
     * methodのPHPDocを付与します
     */
    public function addMethodDoc(): void
    {
        if (empty($this->methodList)) {
            return;
        }

        $addPosition = $this->getAndFormatStartPoint(self::METHOD_PHP_DOC);
        $baseComment = join(PHP_EOL, $this->comments);

        foreach ($this->methodList as $method) {
            if (! preg_match('/' . $method->name . '\(.*/', $baseComment)) {
                $this->addComment(
                    ' * ' . self::METHOD_PHP_DOC . " {$method->returnType} " . ($method->callType ? "{$method->callType} " : '') . "{$method->name}()",
                    $addPosition
                );
                $addPosition =+ 1;
            }
        }
    }

    /**
     * コメントを追加します
     *
     * @param string $comment
     * @param int $addPosition
     * @return void
     */
    private function addComment(string $comment, int $addPosition): void
    {
        array_splice($this->comments, $addPosition, 0, $comment);
        $this->addLineCount += 1;
    }

    /**
     * Docオブジェクトを作成します
     *
     * @return Doc
     */
    public function getDoc(): Doc
    {
        return new Doc(
            join(PHP_EOL, $this->comments),
            $this->doc->getStartLine(),
            $this->doc->getStartFilePos(),
            $this->doc->getStartTokenPos(),
            $this->doc->getEndLine() + $this->addLineCount,
            $this->doc->getEndFilePos() + $this->addLineCount,
            $this->doc->getEndTokenPos() + $this->addLineCount
        );
    }

    /**
     * デフォルトのコメントを作成します
     *
     * @param string $className
     * @return void
     */
    public function setDefaultComment(string $className): void
    {
        $comments = [
            '/**',
            " * class ${className}",
            ' */'
        ];
        $this->setComment(new Doc(
            join(PHP_EOL, $comments)
        ));
    }
}
