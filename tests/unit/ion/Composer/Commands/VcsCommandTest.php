<?php

/*
 * See license information at the package root in LICENSE.md
 */


namespace ion\Composer\Commands;

/**
 * Description of GenerateTemplatesCommandTest
 *
 * @author Justus.Meyer
 */

use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VcsCommandTest extends KernelTestCase {
    
    public function testExecute() {
        
//        $kernel = self::bootKernel();
//        $application = new Application($kernel);
//
//        $application->add(new CreateUserCommand());
//
//        $command = $application->find('app:create-user');
//        $commandTester = new CommandTester($command);
//        $commandTester->execute(array(
//            'command'  => $command->getName(),
//
//            // pass arguments to the helper
//            'username' => 'Wouter',
//
//            // prefix the key with two dashes when passing options,
//            // e.g: '--some-option' => 'option_value',
//        ));
//
//        // the output of the command in the console
//        $output = $commandTester->getDisplay();
//        $this->assertContains('Username: Wouter', $output);

        $this->markTestSkipped('TODO');
    }    
    
}
