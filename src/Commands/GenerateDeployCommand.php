<?php

namespace Deployee\Plugins\GenerateDeploy\Commands;

use Deployee\Components\Config\ConfigInterface;
use Deployee\Components\Environment\EnvironmentInterface;
use Deployee\Plugins\Deploy\Definitions\Deploy\AbstractDeployDefinition;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Helpers;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDeployCommand extends Command
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EnvironmentInterface
     */
    private $env;

    /**
     * @param EnvironmentInterface $env
     */
    public function setEnv(EnvironmentInterface $env)
    {
        $this->env = $env;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function configure()
    {
        parent::configure();
        $this
            ->setName('deploy:generate')
            ->addArgument("name", InputArgument::OPTIONAL, '', uniqid('', false)
        );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $class = sprintf('Deploy_%d_%s', time(), $input->getArgument('name'));
        $class = str_replace(['-', ' '], '_', $class);
        $filePath = $this->config->get('deploy_definition_path') . "/{$class}.php";
        $filePath = strpos($filePath, '/') !== 0 && strpos($filePath, ':') !== 1
            ? $this->env->getWorkDir() . DIRECTORY_SEPARATOR . $filePath
            : $filePath;

        $fileContents = $this->generateFileContents($class);
        if(!file_put_contents($filePath, $fileContents)){
            throw new \RuntimeException('File could not be generated!');
        }

        $output->writeln(sprintf('Generated file %s', $filePath));
    }

    /**
     * @param string $class
     * @return string
     */
    private function generateFileContents(string $class): string
    {
        $generator = new ClassType($class);
        $generator->addExtend(AbstractDeployDefinition::class);
        if(class_exists('Deployee\Plugins\IdeSupport\IdeSupportPlugin')){
            $generator->setComment('@mixin \deployee_ide_helper');
        }
        $method = $generator->addMethod('define');
        $method->setComment('@return void');
        $method->setBody('// Start writing awesome deployments');

        return Helpers::tabsToSpaces(<<<EOL
<?php

{$generator}
EOL
        );
    }
}