<?php

namespace Hyqo\Wire;

use Hyqo\Wire\Loaders\WireLoader;

use \Latte;

class Template
{
    public static $required = [];

    protected static $cacheDir;

    protected static $templatesDir;

    public static function setCacheDir(string $cacheDir): void
    {
        self::$cacheDir = $cacheDir;
    }

    public static function setTemplatesDir(string $templatesDir): void
    {
        self::$templatesDir = $templatesDir;
    }

    public static function compile(string $name, array $data = []): string
    {
        $latte = new Latte\Engine;

        $latte->setLoader(new WireLoader(self::$templatesDir, self::$cacheDir));
        $latte->setTempDirectory(self::$cacheDir . '/latte');

        $set = new Latte\Macros\MacroSet($latte->getCompiler());

        $set->addMacro('wire', null, null, static function () {
        });

        foreach (['var', 'style'] as $tag) {
            $set->addMacro('wire-' . $tag, null, null, function () {
            });
        }

        $set->addMacro('wire-pack', null, null, function (Latte\MacroNode $node) {
            return "echo ' data-wire=\"'; echo htmlspecialchars(json_encode($node->args), ENT_QUOTES); echo '\"';";
        });

        $set->addMacro('wire-class-frontend', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $node->htmlNode->macroAttrs['wire-var'] . '[\'class\'] = ' . Utils::unpack($node->args) . ';';
        });

        $set->addMacro('wire-behavior', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $writer->write($node->htmlNode->macroAttrs['wire-var'] . '[\'behavior\'] = %node.word;');
        });

        $set->addMacro('wire-model', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $writer->write($node->htmlNode->macroAttrs['wire-var'] . '[\'model\'] = %node.word;');
        });

        $set->addMacro('wire-click', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $node->htmlNode->macroAttrs['wire-var'] . '[\'click\'] = "' . $node->args . '";';
        });

        $set->addMacro('wire-submit', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $node->htmlNode->macroAttrs['wire-var'] . '[\'submit\'] = "' . $node->args . '";';
        });

        $set->addMacro(
            'wire-text-frontend',
            null,
            null,
            function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
                return $node->htmlNode->macroAttrs['wire-var'] . '[\'text\'] = ' . Utils::unpack(
                        $node->args
                    ) . ';';
            }
        );

        $set->addMacro(
            'wire-visible-frontend',
            null,
            null,
            function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
                return $node->htmlNode->macroAttrs['wire-var'] . '[\'visible\'] = ' . Utils::unpack(
                        $node->args
                    ) . ';';
            }
        );

        $set->addMacro(
            'wire-visible-backend',
            null,
            null,
            function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
                $styles = addcslashes($node->htmlNode->macroAttrs['wire-style'] ?? '', '\'');

//                unset($node->htmlNode->attrs['style']);

                return <<<PHP
if(array_reduce(
    {$writer->write('%node.array')},
    function(\$result, \$condition){return \$result && \$condition;}, true
    )){
        echo ' style="$styles"';
    } else{
        echo ' style="'.\Hyqo\Wire\Utils::addStyle('$styles', 'display', 'none').'"';
    }
PHP;
            }
        );

        $set->addMacro('wire-state', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            if ($node->args) {
                return $writer->write($node->htmlNode->macroAttrs['wire-var'] . '[\'state\'] = %node.array');
            }

            return '';
        });

        $set->addMacro('wire-init', null, null, function (Latte\MacroNode $node, Latte\PhpWriter $writer) {
            return $writer->write($node->htmlNode->macroAttrs['wire-var'] . ' = %node.array;');
        });

        return $latte->renderToString($name, $data);
    }

    /** @noinspection PhpUnused */
    public static function addRequiredBehavior(string $value): void
    {
        self::addRequired('behavior', $value);
    }

    /** @noinspection PhpUnused */
    public static function addRequiredModel(string $value): void
    {
        self::addRequired('model', $value);
    }

    protected static function addRequired(string $type, string $value): void
    {
        if (!isset(self::$required[$type])) {
            self::$required[$type] = [];
        }

        if (!in_array($value, self::$required[$type], true)) {
            self::$required[$type][] = $value;
        }
    }
}
