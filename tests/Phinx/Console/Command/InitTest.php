<?php

namespace Test\Phinx\Console\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Console\Output\StreamOutput;
use Phinx\Config\Config;
use Phinx\Console\Command\Init;

class InitTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $this->markTestSkipped('This method is breaking Appveyor');
        }
        $files = glob(sys_get_temp_dir() . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function testConfigIsWritten()
    {
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir()
        ));

        $this->assertRegExp(
            '/created (.*)phinx.yml(.*)/',
            $commandTester->getDisplay()
        );

        $this->assertFileExists(
            sys_get_temp_dir() . '/phinx.yml',
            'Phinx configuration not existent'
        );
    }

    /**
     * @expectedException              \InvalidArgumentException
     * @expectedExceptionMessageRegExp /The file "(.*)" already exists/
     */
    public function testThrowsExceptionWhenConfigFilePresent()
    {
        touch(sys_get_temp_dir() . '/phinx.yml');
        $application = new \Phinx\Console\PhinxApplication('testing');
        $application->add(new Init());

        // setup dependencies
        $output = new StreamOutput(fopen('php://memory', 'a', false));

        $command = $application->find('init');

        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'path' => sys_get_temp_dir()
        ));
    }
}
