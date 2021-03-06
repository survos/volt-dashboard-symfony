<?php // generated by @SurvosBase/SidebarMenuSubscriber.php.twig

namespace App\EventSubscriber;

use Survos\BaseBundle\Menu\BaseMenuSubscriber;
use Survos\BaseBundle\Menu\MenuBuilder;
use Survos\BaseBundle\Traits\KnpMenuHelperTrait;
use Survos\BaseBundle\Event\KnpMenuEvent;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


class SidebarMenuSubscriber extends BaseMenuSubscriber implements EventSubscriberInterface
{
    use KnpMenuHelperTrait;

    private AuthorizationCheckerInterface $security;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $bag;

    public function __construct(AuthorizationCheckerInterface $security, ParameterBagInterface $bag)
    {
        $this->security = $security;
        $this->bag = $bag;
    }

    public function onKnpMenuEvent(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();



// https://dashboard.heroku.com/apps/agile-chamber-52782/resources
        // for nested menus, don't add a route, just a label, then use it for the argument to addMenuItem
        $nestedMenu = $this->addMenuItem($menu, ['label' => 'Credits']);
        foreach (['bundles', 'javascript'] as $type) {
            $this->addMenuItem($nestedMenu, [
                'route' => 'survos_base_credits', 'rp' => ['type' => $type], 'icon' => $type, 'label' => ucfirst($type)]);
        }


        $this->addMenuItem($menu, ['route' => 'app_homepage']);
        $this->addMenuItem($menu, ['route' => 'app_menus']);
        $this->addMenuItem($menu, ['route' => 'app_volt_routes']);
        $this->addMenuItem($menu, ['route' => 'app_typography']);
        $this->addMenuItem($menu, ['route' => 'app_heroku']);
        $this->addMenuItem($menu, ['route' => 'app_buttons']);
        $this->addMenuItem($menu, ['route' => 'app_sidebar']);

        $dir = $this->bag->get('kernel.project_dir') . $this->bag->get('volt_dir') . '/src';
        $finder = new Finder();
        foreach ($finder->files()->name('*.html')->in($dir) as $fileInfo) {
            // nested menus
            $parentDir = $fileInfo->getRelativePath();
            $parentMenu = $menus[$parentDir] ?? null;
            if (empty($parentMenu)) {
                if ($parentDir == 'pages') {

                }
                if ($parentDir <> 'pages') {
                    $menus[$parentDir] = $this->addMenuItem($menu, ['label' => $fileInfo->getRelativePath()]);
                } else {
                    $menus[$parentDir] = $menu; // pages go to root.
                }
            }
            // @todo: nest by directory
            $templatePath = str_replace('src', '', $fileInfo->getRelativePath() . '/' . $fileInfo->getFilenameWithoutExtension());
            $this->addMenuItem($menus[$parentDir], ['route' => 'app_legacy_index', 'label' => $fileInfo->getFilenameWithoutExtension(), 'rp' => ['oldRoute' => $templatePath]]);
        }
//        dd(array_keys($menus));


        // add the login/logout menu items.
        $this->authMenu($this->security, $menu);

    }

    /*
    * @return array The event names to listen to
    */
    public static function getSubscribedEvents()
    {
        return [
            MenuBuilder::SIDEBAR_MENU_EVENT => 'onKnpMenuEvent',
        ];
    }
}
