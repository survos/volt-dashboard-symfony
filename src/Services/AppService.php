<?php


namespace App\Services;


use App\Entity\Template;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;

class AppService
{
    private ParameterBagInterface $bag;

    /**
     * AppService constructor.
     */
    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    // convert the HTML to a twig template.

    public function createTemplate(string $html, string $fn, bool $debug=false): Template
    {

        // hack -- list of regexes?
//        $html = preg_replace('|<header.*?/header>|s', '<h2>header moved to course/layout</h2>', $html);
//        $html = preg_replace('|<header.*?/header>|s', '', $html);
//        // per https://getbootstrap.com/docs/4.0/migration/, bump the sizes, since the template uses bs3, not bs4
////        $html = str_replace(['md','sm'], ['lg', 'md'], $html);
////        $html = str_replace(['md'], ['lg'], $html);
//        $html = str_replace(['sm'], ['md'], $html);


        $template = new Template();
        $crawler = new Crawler($html);
//        $title = $crawler->filter('title')->text();


        // see if this is an individual course
        $extends = preg_match('/partials/i', $fn) ? null : "layout.html.twig";
        $template->setExtends($extends);

        foreach (['.content', 'main'] as $selector) {
            $pageContentNode = $crawler->filter($selector);
            if ($pageContentNode->count()) {
                // found something!
                break;
            }
        }

        if ($pageContentNode->count() == 0) {
            $template->body = sprintf("%s<hr/>%s", $fn, $html);
            return $template;
        }


        $nodeValues = $pageContentNode->children()->each(function (Crawler $node, $i) use ($debug) {
//            $header = $node->children()->first();
//            $isH2 = $header->nodeName() == 'h2';
            if ($id = $node->attr('id')) {
//                if (in_array($id, ['quizz-intro-section'])) {
////                    $extends = "Course/layout.html.twig";
//                }
//                dd($id, $node);
            } else {
                $id = "block_" . $i; // $node->text();
            }

            if ($class = (string)$node->attr('class')) {
//                $id = str_replace(" ", "-", $class);
//                return [$class => $node->outerHtml()];
                if (in_array((string)$class, ['footer', 'top-nav'])) {
                    return false;
                }
            }
            if ($id) {
                $content = $node->outerHtml();
                if ($debug) {
                    $content .= <<< END
<h5>${id}</h5>\n
<code>.${class}</code>\n<hr />\n\n
END;
                }
                return ['id' => str_replace('-', '_', $id), 'content' => $content];
            }
//            return $node->outerHtml();
        });

        $body = "{% block meta_title \"$fn\" %}\n\n";
        foreach (array_filter($nodeValues) as $idx => $x) {
            $template->addBlock($blockName = $x['id'], $x['content']);
            $body .= sprintf("  {{ block('%s') }}\n", $blockName);
        }

//        dd($nodeValues);

//        $body = join("<hr />", $nodeValues);
//        dd($nodeValues);
//        $body = $crawler->filter('#page-wrap')->html();
        $template->extends = $extends;
        $template->fn = $fn;
//        $template->extends = "Course/base.html.twig";
        $template->body = $body;
        // @todo: tidy or indenter
        if ($debug) {
            $template->body = sprintf("%s<hr/>%s", $fn, $template->body);
        }
        return $template;
//        dd($title);

    }

    public function getPages()
    {
        $templates = [];
        $dir = $this->bag->get('volt_dir') . '/src';
        $finder = new Finder();
        foreach ($finder->files()->name('*.html')->in($dir) as $fileInfo)
        {
//            $templatePath = $this->bag->get('kernel.project_dir') . '/' . str_replace('src', 'templates', $fileInfo->getRelativePath());
            $templatePath = str_replace('src', '', $fileInfo->getRelativePath() . '/' . $fileInfo->getFilenameWithoutExtension());
            $templates[$templatePath] = $fileInfo;
        }
        return $templates;

    }

}
