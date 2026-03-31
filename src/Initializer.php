<?php

namespace App;

use Composer\Factory;
use Composer\Script\Event;

abstract class Initializer
{
    public static function validateVendor(?string $value): bool
    {
        return (bool) preg_match('/^[a-z0-9]([_.-]?[a-z0-9]+)*$/i', (string) $value);
    }

    public static function validateName(?string $value): bool
    {
        return true;
    }

    public static function validateEmail(?string $value): bool
    {
        return strpos((string) $value, '@') !== false;
    }

    public static function run(Event $event)
    {
        $io = $event->getIO();
        $io->write('Initializing project...');

        $vendorName = $io->askAndValidate('Vendor       : ', function ($value) {
            if (!static::validateVendor($value)) {
                throw new \Exception('Invalid vendor name');
            }

            return $value;
        });
        $packageName = $io->askAndValidate('Package      : ', function ($value) {
            if (!static::validateVendor($value)) {
                throw new \Exception('Invalid package name');
            }

            return $value;
        });
        $authorName = $io->askAndValidate('Author name  : ', function ($value) {
            if (!static::validateName($value)) {
                throw new \Exception('Invalid author name');
            }

            return $value;
        });
        $authorEmail = $io->askAndValidate('Author email : ', function ($value) {
            if (!static::validateEmail($value)) {
                throw new \Exception('Invalid author email');
            }

            return $value;
        });

        $file = Factory::getComposerFile();
        $path = dirname(realpath($file));
        $json = json_decode(file_get_contents($file), true);

        $io->write('- Set new name...');
        $json['name'] = implode('/', [$vendorName, $packageName]);

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
        file_put_contents($file, json_encode($json, JSON_PRETTY_PRINT));

        $io->write('- Modify LICENSE...');
        $licenseFile = $path . DIRECTORY_SEPARATOR . 'LICENSE';
        $license = file($licenseFile);
        $license[0] = sprintf("Copyright %d %s\n", date('Y'), $authorName);
        file_put_contents($licenseFile, implode("", $license));

        $io->write('- Unlink initializer...');

        @unlink(__FILE__);

        $io->write('- Done!');

        $git = $io->askConfirmation('Initialize Git repository (y/N) : ', false);

        if ($git) {
            $io->write('- Initialize Git repository...');
            @exec('git init');
            @exec('git add .');
        }

    }
}
