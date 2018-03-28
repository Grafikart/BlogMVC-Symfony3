<?php
namespace AppBundle\Twig;

use cebe\markdown\GithubMarkdown;
use Twig_Extension;
use Twig_SimpleFilter;

class TwigExtension extends Twig_Extension
{

    private $parser;

    /**
     * TwigExtension constructor.
     * @param $markdown_parser
     */
    public function __construct(GithubMarkdown $markdown_parser) {
      $this->parser = $markdown_parser;
    }

    public function getFilters()
    {
        return [
            new Twig_SimpleFilter('excerpt', [$this, 'excerptFilter']),
            new Twig_SimpleFilter('markdown', [$this, 'markdownFilter'], ['is_safe' => ['all']])
        ];
    }

    public function markdownFilter($content) {
        return $this->parser->parse($content);
    }

    /**
     * Truncate a string
     * @param $content
     * @param string $ending
     * @return string
     */
    public function excerptFilter($content, $max_words = 100, $ending = "...")
    {
        $text = strip_tags($content);
        $words = explode(' ', $text);
        if (count($words) > $max_words) {
            return implode(' ', array_slice($words, 0, $max_words)) . $ending;
        }
        return $text;
    }

    public function getName()
    {
        return 'app_twig_extension';
    }
}
