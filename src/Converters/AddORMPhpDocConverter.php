<?php

declare(strict_types=1);

namespace Bengo4\YiiIdeHelper\Converters;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use Bengo4\YiiIdeHelper\Database\Database;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\ClassPhpDocBuilder;
use Bengo4\YiiIdeHelper\Converters\Builders\PhpDoc\Property;
use Bengo4\YiiIdeHelper\Converters\Printers\PrinterInterface;
use Bengo4\YiiIdeHelper\Converters\Printers\Php7PreservingPrinter;
use Bengo4\YiiIdeHelper\Visitors\AddClassPropertyDocVisitor;
use Bengo4\YiiIdeHelper\Visitors\CollectRelationClassVisitor;

class AddORMPhpDocConverter implements ConverterInterface
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var PrinterInterface
     */
    private $printer;

    /**
     * @param Database $database
     * @param PrinterInterface|null $printer
     */
    public function __construct(Database $database, ?PrinterInterface $printer = null)
    {
        $this->database = $database;
        $this->printer = $printer ?? new Php7PreservingPrinter();
    }

    /**
     * @inheritDoc
     */
    final public function convert(string $filePath, string $code): string
    {
        $stmt = $this->printer->getAst($code);

        $tableName = $this->getTableName($filePath);
        $columnList = $tableName ? $this->getColumnPropertyList($tableName) : [];

        if (empty($columnList)) {
            return '';
        }
        $this->addPropertyDoc($stmt, $columnList);

        return $this->printer->print($stmt);
    }

    /**
     * 構文木に、classのPHPDoc @propertyを追加します
     *
     * @param Node[] $newStmts
     * @param Property[] $columnList
     * @return Node[]
     */
    private function addPropertyDoc(array $newStmts, array $columnList): array
    {
        $propertyList = array_merge($columnList, $this->getRelationProperty($newStmts));
        $builder = new ClassPhpDocBuilder($propertyList);
        $addClassPropertyDocVisitor = new AddClassPropertyDocVisitor($builder);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($addClassPropertyDocVisitor);
        return $traverser->traverse($newStmts);
    }

    /**
     * 構文木から、relationsメソッドに定義されているプロパティを抽出します
     *
     * @param Node[] $newStmts
     * @return Property[]
     */
    private function getRelationProperty(array $newStmts): array
    {
        $collectRelationClassVisitor = new CollectRelationClassVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($collectRelationClassVisitor);
        $traverser->traverse($newStmts);
        return $collectRelationClassVisitor->getPropertyList();
    }

    /**
     * テーブル名からDBに接続し、カラム名と型を取得します
     *
     * @param string $tableName
     * @return Property[]
     */
    private function getColumnPropertyList(string $tableName): array
    {
        return array_map(function ($column) {
            return new Property(
                $column['name'],
                $column['type']
            );
        }, $this->database->getDBColumnData($tableName));
    }

    /**
     * ファイル名から、テーブル名(class名)を取得します。
     *
     * @param string $filePath
     * @return string
     */
    private function getTableName(string $filePath): string
    {
        return str_replace('.php', '', basename($filePath));
    }
}
