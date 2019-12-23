<?php declare(strict_types=1);

namespace Fazland\DoctrineExtra\ORM\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Cast extends FunctionNode
{
    private Node $field;

    private Node $type;

    /**
     * {@inheritdoc}
     */
    public function parse(Parser $parser): void
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);

        $this->field = $parser->StringPrimary();

        $parser->match(Lexer::T_AS);

        $this->type = $parser->StringPrimary();

        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * {@inheritdoc}
     */
    public function getSql(SqlWalker $sqlWalker): string
    {
        return 'CAST('.$this->field->dispatch($sqlWalker).' AS '.$this->type->dispatch($sqlWalker).')';
    }
}
