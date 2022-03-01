<?php

namespace Hyqo\Wire;

use Hyqo\Wire\Part\Block\TagBlock;
use Hyqo\Wire\Part\Block\TextBlock;

class Compiler
{
    public static $replace = [];

    public static $requiredBehaviors = [];
    public static $requiredModels = [];

    public function __construct()
    {
    }

    public static function preprocess(string $string): string
    {
        $string = preg_replace(HtmlEntities::wrapRegex(), '&_$1_;', $string);

        $document = self::loadDocument('<wrap>' . $string . '</wrap>');

        $body = $document->getElementsByTagName('body')->item(0);

        if (!$body) {
            return '';
        }

        $result = self::handleNode($body->firstChild, null);

        $required = '';
        foreach (array_keys(self::$requiredBehaviors) as $value) {
            $required .= "{do Hyqo\\Wire\\Template::addRequiredBehavior('$value')}\n";
        }
        foreach (array_keys(self::$requiredModels) as $value) {
            $required .= "{do Hyqo\\Wire\\Template::addRequiredModel('$value')}\n";
        }

        $result = preg_replace(HtmlEntities::unwrapRegex(), '&$1;', $result);

        return $required . $result;
    }

    private static function loadDocument(string $content): \DOMDocument
    {
        $doc = new \DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="UTF-8">' . $content);
        libxml_clear_errors();

        return $doc;
    }

    private static function handleNode(\DOMNode $node, ?TagBlock $parent): ?TagBlock
    {
        if (!($node instanceof \DOMElement)) {
            return null;
        }

        $block = new TagBlock($node);

        if ($parent) {
            $parent->addChild($block);
        }

        $block->traverse();

        self::handleChildren($node, $block);

        //$block->makeTouch();

        return $block;
    }

    private static function handleChildren(\DOMElement $node, TagBlock $block): void
    {
        foreach ($node->childNodes as $childNode) {
            if ($childNode instanceof \DOMText) {
                $textBlock = new TextBlock($childNode->wholeText);

                $block->addChild($textBlock);
                $textBlock->setParent($block);
            }

            if ($childNode instanceof \DOMElement) {
                self::handleNode($childNode, $block);
            }
        }
    }
}
