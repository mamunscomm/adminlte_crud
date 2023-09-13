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

        // Rewrite home blade file
        $this->rewriteHomeBlade();

        // Clone app files from GIT
        $this->gitClone('app', '');

        // Clone routes files from GIT
        $this->gitClone('routes', '');

        // Clone views files from GIT
        $this->gitClone('resources/views', 'resources/');

        // Clone views files from GIT
        $this->gitClone('config', '');

        $this->runArtisanCommand('config:cache');

        // Clone Form validation request for user
        // $this->gitClone('app/Http/Requests/UserStoreRequest.php', 'Http/Requests');

        // Clone route
        // $this->gitClone('routes/web.php', 'routes');
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


    private function gitClone($filePath, $destinationPath)
    {
        $destinationDirectory = app_path($destinationPath);

        // Create the destination directory if it doesn't exist
        if (!File::isDirectory($destinationDirectory)) {
            File::makeDirectory($destinationDirectory, 0755, true, true);
        }

        // Define the repository and file you want to clone
        $repository = 'https://github.com/mamunscomm/adminlte_crud.git';
        $file = $filePath;

        // Clone the Git repository to a temporary directory
        $tempDirectory = tempnam(sys_get_temp_dir(), 'gitrepo_');
        unlink($tempDirectory);
        mkdir($tempDirectory);

        exec("git clone {$repository} {$tempDirectory}", $output, $returnCode);

        if ($returnCode === 0) {
            $sourceDirectory = $tempDirectory . '/' . $destinationPath;
            $destinationDirectory = base_path($destinationPath);

            // Ensure all parent directories exist
            File::makeDirectory($destinationDirectory, 0755, true, true);

            // Copy the specific directory and its contents to the destination
            $this->copyDirectory($sourceDirectory, $destinationDirectory);

            $this->info("Directory '{$destinationPath}' cloned successfully to '{$destinationDirectory}'");

            // Clean up the temporary directory
            exec("rmdir /s /q {$tempDirectory}");
        } else {
            $this->error("Failed to clone the repository.");
        }
    }



    protected function copyDirectory($source, $destination)
    {
        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $dir = opendir($source);

        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $sourceFile = $source . '/' . $file;
                $destinationFile = $destination . '/' . $file;

                if (is_dir($sourceFile)) {
                    $this->copyDirectory($sourceFile, $destinationFile);
                } else {
                    copy($sourceFile, $destinationFile);
                }
            }
        }

        closedir($dir);
    }
}
