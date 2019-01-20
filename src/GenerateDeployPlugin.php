<?php


namespace Deployee\Plugins\GenerateDeploy;


use Deployee\Components\Application\CommandCollection;
use Deployee\Components\Container\ContainerInterface;
use Deployee\Components\Dependency\ContainerResolver;
use Deployee\Components\Plugins\PluginInterface;
use Deployee\Plugins\GenerateDeploy\Commands\GenerateDeployCommand;

class GenerateDeployPlugin implements PluginInterface
{
    public function boot(ContainerInterface $container)
    {

    }

    public function configure(ContainerInterface $container)
    {
        /* @var ContainerResolver $resolver */
        $resolver = $container->get(ContainerResolver::class);
        $container->extend(CommandCollection::class, function(CommandCollection $collection) use($resolver){
            /* @var GenerateDeployCommand $cmd */
            $cmd = $resolver->createInstance(GenerateDeployCommand::class);
            $collection->addCommand($cmd);

            return $collection;
        });
    }
}