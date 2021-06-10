<?php

namespace App\Console\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\InputStream;

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
        $this->info('Checking docker is installed or not...');
        $checkDockerInstalled = new Process(['which', 'docker']);
        $checkDockerInstalled->run();
        if ($checkDockerInstalled->getOutput() == '') {
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
        } else {
            /**
             * Show message if docker is installed.
             */
            $this->info('Docker is installed.');
        }
    }
}
