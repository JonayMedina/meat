<?php

namespace App\Command;

use App\Message\Sync;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class WelcomeCommand
 * @package App\Command
 * @author Rodmar Zavala <rzavala@praga.ws>
 */
class WelcomeCommand extends Command
{
    protected static $defaultName = 'app:welcome';

    protected function configure()
    {
        $this->setDescription('Welcome message');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $milk = '
 _________________________
< Praga Web Studio Rocks! >
 -------------------------
 \
  \     _________
       | _______ |
      / \         \
     /___\_________\
     |   | \       |
     |   |  \      |
     |   |   \     |
     |   | M  \    |
     |   |     \   |
     |   |\  I  \  |
     |   | \     \ |
     |   |  \  L  \|   &       _,._
     |   |   \     |      __.o`   o`"-.
     |   |    \  K |   .-O o `"-.o   O )_,._
     |   |     \   |  ( o   O  o )--.-"`O   o"-.
     |   |      \  |   \'--------\'  (   o  O    o)
     |___|_______\_|                `----------`';

        $io->success($milk);

        return 0;
    }
}
