<?php

namespace Khasinski\InteractiveConsoleBundle\Command;

use Boris\ExportInspector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Boris\Boris;

class ConsoleCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('shell')
            ->setDescription('Open Boris REPL in application context');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getApplication();
        $kernel = $app->getKernel();
        $container = $kernel->getContainer();

        $app->setCatchExceptions(false);

        $boris = new Boris();
        $boris->setInspector(new ExportInspector());

        $boris->setPrompt($this->getPrompt());
        $boris->setLocal(array(
                'app' => $app,
                'kernel' => $kernel,
                'container' => $container,
                'em' => $container->get('doctrine.orm.entity_manager')
            ) + $this->getServices());

        $boris->start();
    }

    private function getPrompt() {
        return $this->getAppname() . '> ';
    }

    private function getAppname() {
        $app = $this->getApplication();
        return $app->getName() . '-' . $app->getVersion();
    }


    private function getServiceNames() {
        return array('doctrine');
    }

    private function getServices() {
        $container = $this->getApplication()->getKernel()->getContainer();
        $services  = array();

        foreach ($this->getServiceNames() as $key => $name) {
            if (is_numeric($key)) {
                $key = $name;
            }

            if ($container->has($name)) {
                $services[$key] = $container->get($name);
            }
        }

        return $services;
    }
}