<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class ArticleContentSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'ul', 'ol', 'li',
        'h2', 'h3', 'blockquote', 'a', 'pre', 'code',
    ];

    public function sanitize(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML(
            '<?xml encoding="UTF-8"><div id="article-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD,
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $document->getElementById('article-root');

        if (! $root) {
            return '';
        }

        $this->sanitizeChildren($root);

        $sanitized = '';
        foreach ($root->childNodes as $child) {
            $sanitized .= $document->saveHTML($child);
        }

        return $sanitized;
    }

    private function sanitizeChildren(DOMNode $node): void
    {
        foreach (iterator_to_array($node->childNodes) as $child) {
            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'template'], true)) {
                $node->removeChild($child);

                continue;
            }

            $this->sanitizeChildren($child);

            if (! in_array($tag, self::ALLOWED_TAGS, true)) {
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);

                continue;
            }

            $this->sanitizeAttributes($child, $tag);
        }
    }

    private function sanitizeAttributes(DOMElement $element, string $tag): void
    {
        $href = $tag === 'a' ? $element->getAttribute('href') : null;

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $element->removeAttributeNode($attribute);
        }

        if ($href !== null && $this->isSafeHref($href)) {
            $element->setAttribute('href', $href);
            $element->setAttribute('rel', 'nofollow noopener noreferrer');
        }
    }

    private function isSafeHref(string $href): bool
    {
        $href = trim($href);

        if ($href === '' || str_starts_with($href, '//')) {
            return false;
        }

        if (str_starts_with($href, '/') || str_starts_with($href, '#')) {
            return true;
        }

        return in_array(strtolower(parse_url($href, PHP_URL_SCHEME) ?? ''), ['http', 'https', 'mailto'], true);
    }
}
