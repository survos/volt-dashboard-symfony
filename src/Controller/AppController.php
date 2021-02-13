<?php

namespace App\Controller;

use App\Entity\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class AppController extends AbstractController
{
    private ParameterBagInterface $bag;

    /**
     * AppController constructor.
     */
    public function __construct(ParameterBagInterface $bag)
    {
        $this->bag = $bag;
    }

    /**
     * @Route("/", name="app_homepage")
     * @Route("/welcome", name="adminlte_welcome")
     */
    public function index(): Response
    {
        return $this->render('app/homepage.html.twig', [
            'controller_name' => 'AppController',
        ]);
    }

    private function getPages()
    {
        $templates = [];
        $dir = $this->bag->get('volt_dir') . '/src';
        $finder = new Finder();
        foreach ($finder->files()->name('*.html')->in($dir) as $fileInfo)
        {
//            $templatePath = $this->bag->get('kernel.project_dir') . '/' . str_replace('src', 'templates', $fileInfo->getRelativePath());
            $templatePath = str_replace('src', '', $fileInfo->getRelativePath() . '/' . $fileInfo->getFilenameWithoutExtension());
            array_push($templates, $templatePath);

//            dd($templatePath, $fileInfo->getRelativePath(), $fileInfo->getRelativePathname(), $fileInfo->getFilename(), $fileInfo);
//            if (!is_dir($templatePath)) {
//                mkdir($templatePath, 0777, true);
//            }
//            $templateRealPath = $templatePath . '/' . $fileInfo->getFilename() . '.twig';
//            if (!file_exists($templateRealPath)) {
//                file_put_contents($templateRealPath, $fileInfo->getContents());
//            }
//            dd($fileInfo, $fileInfo->getRelativePath(), $fileInfo->getRelativePathname());
//            $twigFilename = $fileInfo;
        }
        return $templates;

    }

    /**
     * @Route("/buttons", name="app_buttons")
     */
    public function buttons(): Response
    {
        return $this->render('_dynamic/pages/components/buttons.html.twig', []);
    }

    /**
     * @Route("/volt_routes", name="app_volt_routes")
     */
    public function volt_routes(): Response
    {
        return $this->render('app/_test_routes.html.twig', [
            'pages' => $this->getPages()
        ]);
    }

    /**
     * @Route("/_sidebar", name="app_sidebar")
     */
    public function sidebar(): Response
    {
        return new Response('Sidebar response!!');
        return $this->render('_dynamic/partials/dashboard/_sidenav.html.twig', []);
    }

    /**
     * @Route("/{oldRoute}.html", name="app_legacy_index", requirements={"oldRoute"=".+"})
     */
    public function legacyIndex(Environment $twig, RouterInterface $router, ParameterBagInterface $bag, Request $request, $oldRoute): Response
    {

        $root = $bag->get('kernel.project_dir') . '/templates';
        if (!file_exists($fn = sprintf("%s/src/%s.html", $bag->get('volt_dir'), $oldRoute)))
        {
            dd("Missing " . $fn, $oldRoute);
        }
        $html = file_get_contents($fn);
        $route = $router->getRouteCollection()->get($oldRoute);
//            dd($route);
//        try {
//        } catch (\Exception $exception)
//        {
//            dd($oldRoute);
//        }

        $template = $this->createTemplate($html, $oldRoute);
        $source = $template->__toTwig();

        $templateRelativePath = '/_dynamic/' . dirname($oldRoute);
        $templatePath = $root . $templateRelativePath; // str_replace('src', 'templates', $oldRoute);
        if (!is_dir($templatePath)) {
            mkdir($templatePath, 0777, true);
        }

        $twigName = $templateRelativePath . '/' . sprintf('%s.html.twig', pathinfo($oldRoute, PATHINFO_FILENAME));
            file_put_contents($root . $twigName, $source);
        if (!file_exists($templatePath)) {
        }
//        dd($twigName);
//        dd($templatePath, $twigName);

//        $twigTemplate = $twig->createTemplate($source);
//        $adminViewOfTemplate =  $twig->render('app/landing.html.twig', ['title' => $oldRoute]);
        // calling this does something to the global $twig, preventing the regular rendering.
//        $renderedHtml = $twigTemplate->render([]);
        return $this->render($twigName, [
            'html' => $html,
            'fn' => $fn,
            'oldRoute' => $oldRoute,
            'route' => $route,
            'sectionIdx' => 1,
            'subIdx' => 1,
            'section' => [
                'header' => 'Section Header',
                'subsections' => [
                    'header' => 'Subsection 1'
                ]
            ]
        ]);

//        return new Response($adminViewOfTemplate);
//        return new Response($renderedHtml);
//
//
////        return $this->render(, [
////            'title' => $oldRoute
////        ]);
////        dd($renderedHtml, $template);
//
//
//        //
//        dd($oldRoute);
    }

    private function createTemplate($html, $fn, $debug=false): Template
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
                break; //
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
        $template->body = sprintf("%s<hr/>%s", $fn, $body);
        return $template;
//        dd($title);

    }
}
