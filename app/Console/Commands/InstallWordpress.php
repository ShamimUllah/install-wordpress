<?php

namespace App\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

use Illuminate\Console\Command;

class InstallWordpress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install:wordpress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will install wordpress';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * Check docker is installed or not.
         */

        $this->info('Checking docker is installed or not...');
        $checkDockerInstalled = new Process(['which', 'docker']);
        $checkDockerInstalled->run();

        if ($checkDockerInstalled->getOutput() == '') {

            /**
             * Install docker.
             */

            $this->installDocker();
        } else {
            /**
             * Check docker-compose is installed or not.
             */

            $this->info('Checking docker-compose is installed or not...');
            $checkDockerComposeInstalled = new Process(['which', 'docker-compose']);

            $checkDockerComposeInstalled->run();
            if ($checkDockerComposeInstalled->getOutput() == '') {
                $this->installDockerCompose();
            } else {
                $this->info('docker-compose is already installed.');

                $siteTitle = 'Wordpress'; /* Set default site title */
                $domainName = 'example.com'; /* Set default domain name */

                $siteTitle = $this->ask('Enter wordpress site name?');
                $domainName = $this->ask('Enter wordpress domain name (Ex:example.com)?');

                if (preg_match("/^([a-zA-Z0-9][a-zA-Z0-9-_]*\.)*[a-zA-Z0-9]*[a-zA-Z0-9-_]*[[a-zA-Z0-9]+$/", $domainName) == FALSE) {
                    $domainName = $this->ask('Enter valid domain name (Ex:example.com)?');
                };
                $this->installWp($siteTitle, $domainName);
            }
        }
    }

    protected function installDocker()
    {
        /**
         * Run commands if docker is not installed.
         */

        $installDockerStep1 = new Process(['sudo', 'apt-get', 'install', 'apt-transport-https', 'ca-certificates', 'curl', 'gnupg', 'lsb-release', '-y']);
        $installDockerStep1->run();
        if (!$installDockerStep1->isSuccessful()) {
            $this->warn(new ProcessFailedException($installDockerStep1));
        }
        $this->info($installDockerStep1->getOutput());


        $installDockerStep2 = new Process(['curl', '-fsSL', 'https://download.docker.com/linux/ubuntu/gpg']);
        $installDockerStep2->start();

        $installDockerStep2->wait(function ($type, $buffer) {
            $installDockerStep3 = new Process(['sudo', 'gpg', '--dearmor', '-o', '/usr/share/keyrings/docker-archive-keyring.gpg']);

            $installDockerStep3->setInput($buffer);
            $installDockerStep3->run();
            if (!$installDockerStep3->isSuccessful()) {
                $this->warn(new ProcessFailedException($installDockerStep3));
            }

            $this->info($installDockerStep3->getOutput());
        });
        $this->info($installDockerStep2->getOutput());


        $installDockerStep4 = new Process(['echo', 'deb', '[arch=amd64 signed-by=/usr/share/keyrings/docker-archive-keyring.gpg]', 'https://download.docker.com/linux/ubuntu', 'focal', 'stable']);
        $installDockerStep4->start();

        $installDockerStep4->wait(function ($type, $buffer) {
            $installDockerStep5 = new Process(['sudo', 'tee', '/etc/apt/sources.list.d/docker.list', '>', '/dev/null']);

            $installDockerStep5->setInput($buffer);
            $installDockerStep5->run();
            if (!$installDockerStep5->isSuccessful()) {
                $this->warn(new ProcessFailedException($installDockerStep5));
            }

            $this->info($installDockerStep5->getOutput());
        });
        $this->info($installDockerStep4->getOutput());



        $installDockerStep6 = new Process(['sudo', 'apt-get', 'update', '-y']);
        $installDockerStep6->run();
        if (!$installDockerStep6->isSuccessful()) {
            $this->warn(new ProcessFailedException($installDockerStep6));
        }

        $this->info($installDockerStep6->getOutput());

        $installDockerStep7 = new Process(['sudo', 'apt-get', 'install', 'docker-ce', 'docker-ce-cli', 'containerd.io', '-y']);

        $installDockerStep7->run();
        if (!$installDockerStep7->isSuccessful()) {
            $this->warn(new ProcessFailedException($installDockerStep7));
        }
        $this->info($installDockerStep7->getOutput());
    }

    protected function installDockerCompose()
    {
        /**
         * Run commands if docker-compose is not installed.
         */


        $installDockerComposeStep1 = new Process(['sudo', 'curl', '-L', 'https://github.com/docker/compose/releases/download/1.29.2/docker-compose-linux-x86_64', '-o', '/usr/local/bin/docker-compose']);

        $installDockerComposeStep1->run();
        if (!$installDockerComposeStep1->isSuccessful()) {
            $this->warn(new ProcessFailedException($installDockerComposeStep1));
        }

        $this->info($installDockerComposeStep1->getOutput());

        $installDockerComposeStep2 = new Process(['sudo', 'chmod', '+x', '/usr/local/bin/docker-compose']);

        $installDockerComposeStep2->run();
        if (!$installDockerComposeStep2->isSuccessful()) {
            $this->warn(new ProcessFailedException($installDockerComposeStep2));
        }

        $this->info($installDockerComposeStep2->getOutput());
    }

    protected function installWp($siteTitle, $domainName)
    {
        /**
         * Installing Wordpress
         */
        $this->info('Installing wordpress...');
        $composeFile = public_path('wordpressfiles/docker-compose.yml');
        $insrallWP = new Process(['sudo', 'WP_TITLE="' . $siteTitle . '"', 'DOMAIN_NAME=' . $domainName . '', 'docker-compose', '-f', $composeFile, 'up', '-d']);
        $insrallWP->setTimeout(3600);
        $insrallWP->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                $this->warn($buffer);
            } else {
                $this->info($buffer);
            }
        });
        $this->info($composeFile);
        if (!$insrallWP->isSuccessful()) {
            $this->warn(new ProcessFailedException($insrallWP));
        }
        $this->info($insrallWP->getOutput());


        $this->info('Editing /etc/host file...');


        $etcText = new Process(['echo', '127.0.0.1 ' . $domainName]);
        $etcText->start();

        $etcText->wait(function ($type, $buffer) {
            $addingEtc = new Process(['sudo', 'tee', '-a', '/etc/hosts']);

            $addingEtc->setInput($buffer);
            $addingEtc->run();
            if (!$addingEtc->isSuccessful()) {
                $this->warn(new ProcessFailedException($addingEtc));
            }

            $this->info($addingEtc->getOutput());
        });

        sleep(30);

        $this->info('Starting website: http://' . $domainName);
    }
}
