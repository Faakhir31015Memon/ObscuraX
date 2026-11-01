<?php

declare(strict_types=1);

namespace App\Core;

use DOMDocument;
use DOMElement;
use DOMNode;

/**
 * Allow-list HTML sanitizer for blog content.
 * Everything not explicitly permitted is removed before the content is stored.
 */
final class Sanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'hr', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong', 'b', 'em', 'i', 'u', 's', 'strike',
        'blockquote', 'pre', 'code', 'ul', 'ol', 'li', 'a', 'img', 'figure', 'figcaption',
        'span', 'div', 'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'sub', 'sup',
    ];

    private const ALLOWED_ATTRIBUTES = [
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height', 'loading'],
        'span' => ['class'],
        'p' => ['class'],
        'div' => ['class'],
        'pre' => ['class'],
        'code' => ['class'],
        'th' => ['colspan', 'rowspan'],
        'td' => ['colspan', 'rowspan'],
    ];

    public static function html(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        $html = mb_scrub($html, 'UTF-8');

        // Remove dangerous blocks before the DOM pass as a second layer of defence.
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base)[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $html = preg_replace('#<(script|style|iframe|object|embed|form|input|button|meta|link|base)[^>]*/?>#is', '', $html) ?? $html;

        $dom = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="obscurax-root">' . $html . '</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        $root = $dom->getElementById('obscurax-root');
        if (!$root instanceof DOMElement) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }

        self::cleanChildren($root);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output);
    }

    private static function cleanChildren(DOMNode $node): void
    {
        $child = $node->firstChild;
        while ($child !== null) {
            $next = $child->nextSibling;

            if ($child instanceof DOMElement) {
                $tag = strtolower($child->nodeName);

                if (!in_array($tag, self::ALLOWED_TAGS, true)) {
                    // Keep text content of unknown formatting wrappers, drop the wrapper.
                    if (in_array($tag, ['font', 'center', 'small', 'big', 'section', 'article', 'header', 'footer'], true)) {
                        self::cleanChildren($child);
                        while ($child->firstChild !== null) {
                            $node->insertBefore($child->firstChild, $child);
                        }
                    }
                    $node->removeChild($child);
                    $child = $next;
                    continue;
                }

                $allowed = self::ALLOWED_ATTRIBUTES[$tag] ?? [];
                $attributes = [];
                foreach ($child->attributes as $attribute) {
                    $attributes[] = $attribute->name;
                }
                foreach ($attributes as $name) {
                    if (!in_array(strtolower($name), $allowed, true)) {
                        $child->removeAttribute($name);
                    }
                }

                if ($child->hasAttribute('href')) {
                    $href = trim($child->getAttribute('href'));
                    if (!self::isSafeUrl($href)) {
                        $child->removeAttribute('href');
                    } else {
                        $child->setAttribute('rel', 'nofollow noopener noreferrer');
                        if ($child->getAttribute('target') === '_blank') {
                            $child->setAttribute('target', '_blank');
                        }
                    }
                }

                if ($child->hasAttribute('src')) {
                    $src = trim($child->getAttribute('src'));
                    if (!self::isSafeImageSrc($src)) {
                        $node->removeChild($child);
                        $child = $next;
                        continue;
                    }
                    $child->setAttribute('loading', 'lazy');
                }

                self::cleanChildren($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                $node->removeChild($child);
            }

            $child = $next;
        }
    }

    private static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || str_starts_with($url, '#')) {
            return true;
        }
        if (preg_match('#^(https?:)?//#i', $url)) {
            return true;
        }
        if (preg_match('#^(mailto|tel):#i', $url)) {
            return true;
        }
        return str_starts_with($url, '/');
    }

    private static function isSafeImageSrc(string $url): bool
    {
        if ($url === '') {
            return false;
        }
        if (str_starts_with($url, '/')) {
            return true;
        }
        return (bool) preg_match('#^https?://#i', $url);
    }
}
