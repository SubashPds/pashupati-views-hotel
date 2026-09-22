<?php

namespace App\Support;

use DOMDocument;
use DOMElement;
use DOMNode;

class SafeHtml
{
    private const TAGS = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'blockquote', 'span', 'div', 'table', 'thead', 'tbody', 'tr', 'td', 'th', 'img', 'figure', 'figcaption', 'small', 'sub', 'sup', 'code', 'pre', 'hr', 'strike', 'mark'];
    private const DROP = ['script', 'style', 'iframe', 'object', 'embed', 'svg', 'math', 'form', 'input', 'button', 'textarea', 'select', 'option', 'template', 'noscript'];

    public static function clean(?string $html): string
    {
        if (! $html) return '';
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><html><body>'.$html.'</body></html>', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
            $body = $document->getElementsByTagName('body')->item(0);
            return $body ? self::children($body) : '';
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    }

    private static function children(DOMNode $node): string
    {
        $result = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $result .= e($child->nodeValue);
                continue;
            }
            if (! $child instanceof DOMElement) continue;
            $tag = strtolower($child->tagName);
            if (in_array($tag, self::DROP, true)) continue;
            $content = self::children($child);
            if (! in_array($tag, self::TAGS, true)) {
                $result .= $content;
                continue;
            }
            $attributes = '';
            foreach (['title', 'alt', 'href', 'src', 'width', 'height', 'colspan', 'rowspan'] as $name) {
                if (! $child->hasAttribute($name)) continue;
                $value = $child->getAttribute($name);
                $allowed = match ($name) {
                    'title' => true,
                    'alt' => $tag === 'img',
                    'href' => $tag === 'a' && self::safeUrl($value, true),
                    'src' => $tag === 'img' && self::safeUrl($value),
                    'width', 'height' => $tag === 'img' && ctype_digit($value) && (int) $value <= 4096,
                    'colspan', 'rowspan' => in_array($tag, ['td', 'th'], true) && ctype_digit($value) && (int) $value <= 20,
                };
                if ($allowed) $attributes .= ' '.$name.'="'.e($value).'"';
            }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $attributes .= ' target="_blank" rel="noopener noreferrer"';
            }
            $result .= '<'.$tag.$attributes.'>';
            if (! in_array($tag, ['br', 'img', 'hr'], true)) $result .= $content.'</'.$tag.'>';
        }
        return $result;
    }

    private static function safeUrl(string $url, bool $link = false): bool
    {
        if ($url === '' || preg_match('/[\x00-\x20\x7f-\x9f\\\\]/', $url)) return false;
        return (bool) preg_match($link ? '~^(?:https?://|/(?!/)|\#|mailto:|tel:)~i' : '~^(?:https?://|/(?!/))~i', $url);
    }
}
