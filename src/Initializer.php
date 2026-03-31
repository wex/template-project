<?php

namespace Phi;

use Composer\Factory;
use Composer\Script\Event;

abstract class Initializer
{
    public static function run(Event $event)
    {
        $io = $event->getIO();
        $io->write('Initializing project...');

        while (!($authorName = $io->ask('Author name: ')));
        while (!($authorEmail = $io->ask('Author email: ')));

        $file = Factory::getComposerFile();
        $path = dirname(realpath($file));
        $json = json_decode(file_get_contents($file), true);

        $io->write('- Modyfing author...');

        $json['authors'] = [
            [
                'name' => $authorName,
                'email' => $authorEmail,
            ],
        ];

        $io->write('- Removing unnecessary dependencies...');

        unset($json['require-dev']['composer/composer']);

        $io->write('- Removing unnecessary scripts...');

        unset($json['scripts']['post-create-project-cmd']);

        $io->write('- Saving new composer.json...');

        file_put_contents($file . '.new', json_encode($json, JSON_PRETTY_PRINT));

        $io->write('- Modify LICENSE...');

        $licenseFile = $path . DIRECTORY_SEPARATOR . 'LICENSE';
        $license = file($licenseFile);
        $license[0] = sprintf("Copyright %d %s\n", date('Y'), $authorName);
        file_put_contents($licenseFile . '.new', implode("", $license));

        $io->write('- Unlink initializer...');

        @unlink(__FILE__);

        $io->write('- Done!');

    }
}
