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
use Bengo4\YiiIdeHelper\Visitors\AddClassDocVisitor;
use Bengo4\YiiIdeHelper\Visitors\CollectRelationClassVisitor;
use Bengo4\YiiIdeHelper\Visitors\CollectScopeMethodVisitor;

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
        $columnList = $this->getColumnPropertyList($tableName) ?? [];

        $this->addPhpDoc($stmt, $columnList);

        return $this->printer->print($stmt);
    }

    /**
     * 構文木に、classのPHPDoc @propertyを追加します
     *
     * @param Node[] $newStmts
     * @param Property[] $columnList
     * @return Node[]
     */
    private function addPhpDoc(array $newStmts, array $columnList): array
    {
        $propertyList = array_merge($columnList, $this->createRelationPropertyList($newStmts, $columnList));
        $methodList = $this->getScopeMethod($newStmts);

        if (empty($propertyList) && empty($methodList)) {
            return $newStmts;
        }

        $builder = new ClassPhpDocBuilder();
        $builder->setPropertyList($propertyList);
        $builder->setMethodList($methodList);

        $addClassDocVisitor = new AddClassDocVisitor($builder);

        $traverser = new NodeTraverser();
        $traverser->addVisitor($addClassDocVisitor);
        return $traverser->traverse($newStmts);
    }

    /**
     * 構文木から、relationsメソッドに定義されているプロパティを抽出します
     *
     * @param Node[] $newStmts
     * @return Property[]
     */
    private function createRelationPropertyList(array $newStmts): array
    {
        $traverser = new NodeTraverser();
        $collectVisitor = new CollectRelationClassVisitor();
        $collectVisitor->setHasNamespace($namespaceVisitor->hasNameSpace());
        $traverser->addVisitor($collectVisitor);

        $traverser->traverse($newStmts);
        return $collectVisitor->getPropertyList();
    }

    /**
     * 構文木から、scopesメソッドに定義されているプロパティを抽出します
     *
     * @param Node[] $newStmts
     * @return Property[]
     */
    private function getScopeMethod(array $newStmts): array
    {
        $collectVisitor = new CollectScopeMethodVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($collectVisitor);
        $traverser->traverse($newStmts);
        return $collectVisitor->getMethodList();
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
