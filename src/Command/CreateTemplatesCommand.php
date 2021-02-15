<?php

namespace App\Command;

use App\Services\AppService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class CreateTemplatesCommand extends Command
{
    protected static $defaultName = 'app:create-templates';
    protected static $defaultDescription = 'Add a short description for your command';
    private string $root;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $bag;
    /**
     * @var AppService
     */
    private AppService $appService;

    /**
     * CreateTemplatesCommand constructor.
     */

    public function __construct(ParameterBagInterface $bag, AppService $appService)
    {
        parent::__construct();
        $this->root = $bag->get('kernel.project_dir');


        $this->bag = $bag;
        $this->appService = $appService;
    }


    protected function configure()
    {
        $this
            ->setDescription(self::$defaultDescription)
            ->addArgument('dir', InputArgument::OPTIONAL, 'Directory where volt admin template is located', )
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var SplFileInfo $fileInfo
         */
        foreach ($this->appService->getPages() as $page => $fileInfo)
        {
            $template = $this->appService->createTemplate($fileInfo->getContents(), $fileInfo->getRealPath());
        }
        $dir = $this->bag->get('volt_dir');
        $finder = new Finder();
        foreach ($finder->files()->name('*.html')->in($dir) as $fileInfo)
        {

            $templatePath = $this->root . '/' . str_replace('src', 'templates', $fileInfo->getRelativePath());
            if (!is_dir($templatePath)) {
                $io->warning("Creating " . $templatePath);
                mkdir($templatePath, 0777, true);
            }
            $templateRealPath = $templatePath . '/' . $fileInfo->getFilename() . '.twig';
            if (!file_exists($templateRealPath)) {
                file_put_contents($templateRealPath, $fileInfo->getContents());
            }
            dd($fileInfo, $fileInfo->getRelativePath(), $fileInfo->getRelativePathname());
            $twigFilename = $fileInfo;
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('Templates have been generated.');

        return Command::SUCCESS;
    }
}
