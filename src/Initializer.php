<?php

namespace Phi;

use Composer\Script\Event;

abstract class Initializer
{
    public static function run(Event $event)
    {
        $io = $event->getIO();
        $io->write('Initializing project...');

        while (($authorName = $io->ask('Author name: ')) === '');
        while (($authorEmail = $io->ask('Author email: ')) === '');

        $io->write("Hello, {$authorName} ({$authorEmail})!");
    }
}
