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
     * @var string
     */
    private const PROPERTY_PHP_DOC = '@property';

    /**
     * @param Property[] $propertyList
     */
    public function __construct(array $propertyList)
    {
        $this->propertyList = $propertyList;
    }

    /**
     * propertyのPHPDocを付与します
     *
     * @return Doc
     */
    public function createPropertyDoc(): Doc
    {
        $addPosition = count($this->comments) - 1;
        $defaultPosition = true;

        for ($i = 0; $i < count($this->comments); $i++) {
            if (strpos($this->comments[$i], self::PROPERTY_PHP_DOC)) {
                $addPosition = $i + 1;
                $defaultPosition = false;
            }
        }
        if ($defaultPosition) {
            $this->addComment(
                ' *',
                $addPosition
            );
        }

        $baseComment = join(PHP_EOL, $this->comments);
        foreach ($this->propertyList as $property) {
            if (! preg_match('/\$' . $property->name . '( |'. PHP_EOL . ').*/', $baseComment)) {
                $this->addComment(
                    ' * ' . self::PROPERTY_PHP_DOC . ' ' . $property->type . ' $' . $property->name,
                    $addPosition + $this->addLineCount
                );
            }
        }
        return $this->createDoc();
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
    private function createDoc(): Doc
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
