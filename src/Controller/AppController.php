<?php

namespace App\Controller;

use App\Entity\Template;
use App\Services\AppService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
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


    /**
     * @Route("/buttons", name="app_buttons")
     */
    public function buttons(): Response
    {
        return $this->render('_dynamic/pages/components/buttons.html.twig', []);
    }

    /**
     * @Route("/menus", name="app_menus")
     */
    public function menus(): Response
    {
        return $this->render('app/menus.html.twig', [

        ]);
    }

    /**
     * @Route("/volt_routes", name="app_volt_routes")
     */
    public function volt_routes(AppService $appService): Response
    {
        $templates = [];
        /**
         * @var SplFileInfo $fileInfo
         */
        foreach ($appService->getPages() as $realPath => $fileInfo) {
            $template = $appService->createTemplate($fileInfo->getContents(), $realPath, false);
            $templates[$fileInfo->getRelativePath()] = $template;
        }
        return $this->render('app/templates.html.twig', [
            'templates' => $templates,
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
    public function legacyIndex(Environment $twig, AppService $appService, RouterInterface $router, ParameterBagInterface $bag, Request $request, $oldRoute): Response
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

        $template = $appService->createTemplate($html, $oldRoute);
        $source = $template->toTwig();

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

}
