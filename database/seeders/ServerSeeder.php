<?php

namespace Database\Seeders;

use App\Models\Server;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $servers = [
            [
                'name'        => 'web-01',
                'hostname'    => 'web-01.infrapulse.local',
                'ip_address'  => '192.168.1.10',
                'environment' => 'production',
                'description' => 'Primary web server — handles inbound HTTP/HTTPS traffic',
            ],
            [
                'name'        => 'web-02',
                'hostname'    => 'web-02.infrapulse.local',
                'ip_address'  => '192.168.1.11',
                'environment' => 'production',
                'description' => 'Secondary web server — load balanced with web-01',
            ],
            [
                'name'        => 'db-01',
                'hostname'    => 'db-01.infrapulse.local',
                'ip_address'  => '192.168.1.20',
                'environment' => 'production',
                'description' => 'Primary database server — PostgreSQL master node',
            ],
            [
                'name'        => 'cache-01',
                'hostname'    => 'cache-01.infrapulse.staging',
                'ip_address'  => '10.0.1.30',
                'environment' => 'staging',
                'description' => 'Redis cache server — staging environment',
            ],
            [
                'name'        => 'worker-01',
                'hostname'    => 'worker-01.infrapulse.local',
                'ip_address'  => '192.168.1.40',
                'environment' => 'production',
                'description' => 'Background job worker — queue processing node',
            ],
            [
                'name'        => 'api-dev',
                'hostname'    => 'api.infrapulse.dev',
                'ip_address'  => '10.0.0.5',
                'environment' => 'development',
                'description' => 'Dev API server — feature branch testing',
            ],
        ];

        foreach ($servers as $server) {
            Server::updateOrCreate(['name' => $server['name']], $server);
        }
    }
}
