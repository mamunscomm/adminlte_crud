<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Process\Process;
use File;
use Illuminate\Support\Facades\Hash;

class SetupAdminLte extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'setup:adminlte';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Customize Admin Lte template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Run composer & artisan commands to install adminlte laravel package
        $this->info('Running composer command jeroennoten/laravel-adminlte...');
        $this->runComposerRequire('jeroennoten/laravel-adminlte');
        $this->info('Installed jeroennoten/laravel-adminlte package');

        $this->info('Running artisan command adminlte:install...');
        $this->runArtisanCommand('adminlte:install');
        $this->info('Installed adminlte');

        $this->info('Running composer command laravel/ui...');
        $this->runComposerRequire('laravel/ui');
        $this->info('Installed laravel/ui');

        $this->info('Running artisan command ui bootstrap --auth...');
        $this->runArtisanCommand('ui', ['bootstrap', '--auth']);
        $this->info('Installed ui bootstrap --auth');

        $this->info('Running artisan command adminlte:install --only=auth_views...');
        $this->runArtisanCommand('adminlte:install', ['--only=auth_views'], 'yes');
        $this->info('Installed adminlte:install --only=auth_views');

        $this->info('Running artisan command adminlte:install --only=main_views...');
        $this->runArtisanCommand('adminlte:install', ['--only=main_views']);
        $this->info('Installed adminlte:install --only=main_views');

        // Create directory for 404 page
        $this->createBladeFile();

        // Run config:cache
        $this->info('Running artisan command config:cache...');
        $this->runArtisanCommand('config:cache');
        $this->info('Cache config successfully');

        // Run migration
        $this->info('Running artisan command migrate...');
        $this->runArtisanCommand('migrate');
        $this->info('Migrate successfully');
        // Create user seeder
        // $this->info('Running artisan command make: seeder UserSeeder...');
        // $this->runArtisanCommand('make:seeder', ['UserSeeder']);
        // $this->info('Seeder created successfully');

        // Insert 2 users to seeder file
        $this->insertUsersToDB();
        // Run seeder class

        // Create user controller
        $this->info('Running artisan command make:controller UserController');
        $this->runArtisanCommand('make:controller', ['UserController']);
        $this->info('Controller created successfully');

        // Rewrite home blade file
        $this->rewriteHomeBlade();
    }


    private function runComposerRequire($package)
    {
        $process = new Process(['composer', 'require', $package]);
        $process->setWorkingDirectory(base_path());
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });
    }

    private function runArtisanCommand($command, $options = [], $ans = 'no')
    {
        // $process = new Process(['php', 'artisan', $command]);
        $commandWithArgs = array_merge(['php', 'artisan', $command], $options);
        $process = new Process($commandWithArgs);

        if($ans == 'yes'){
            $process->setInput("yes\n");
        }
        $process->setTimeout(3600);

        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });
    }

    private function createBladeFile()
    {
        $path = "resources/views/errors/404.blade.php";
        $fileText = "
            @extends('adminlte::page')

            @section('title', 'Dashboard')

            @section('content_header')
                <h1>Page not found</h1>
            @stop

            @section('content')
                <div class='error-page'>
                    <h2 class='headline text-warning'> 404</h2>
                    <div class='error-content'>
                        <h3><i class='fas fa-exclamation-triangle text-warning'></i> Oops! Page not found.</h3>
                        <p>
                            We could not find the page you were looking for.
                            Meanwhile, you may <a href='../../index.html'>return to dashboard</a> or try using the search form.
                        </p>
                    </div>
                </div>
            @stop

            @section('css')
                <link rel='stylesheet' href='/css/admin_custom.css'>
            @stop

            @section('js')
                <script>
                    console.log('Hi!');
                </script>
            @stop
        ";

        $this->createDir($path);

        if (File::exists($path))
        {
            $this->error("File {$path} already exists!");
            return;
        }

        File::put($path, $fileText);

        $this->info("File {$path} created.");
    }


    public function createDir($path)
    {
        $dir = dirname($path);

        if (!file_exists($dir))
        {
            mkdir($dir, 0777, true);
        }
    }

    private function insertUsersToDB()
    {
        $users = [
            [
                'name' => 'Abdullah Al Mamun',
                'email' => 'mamun.abdullah@summitcommunications.net',
                'password' => Hash::make('Scomm@123'),
            ],
            [
                'name' => 'Demo User',
                'email' => 'demo@scomm.net',
                'password' => Hash::make('Demo@123'),
            ],
        ];

        User::insert($users);

        $this->info('Two users inserted into the database.');
    }

    private function rewriteHomeBlade()
    {
        $path = "resources/views/home.blade.php";

        $fileText = "
            @extends('adminlte::page')

            @section('title', 'Dashboard')

            @section('content_header')
                <h1>Dashboard</h1>
            @stop

            @section('content')
                <p>Welcome to this beautiful admin panel.</p>
            @stop

            @section('css')
                <link rel='stylesheet' href='/css/admin_custom.css'>
            @stop

            @section('js')
                <script> console.log('Hi!'); </script>
            @stop

        ";

        File::put($path, $fileText);

        $this->info("File {$path} updated.");
    }
}
