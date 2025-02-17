<?php

namespace App\Jobs;

use App\Actions\Proxy\StartProxy;
use App\Models\Server;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProxyCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
    }

    public function handle()
    {
        try {
            $container_name = 'coolify-proxy';
            $servers = Server::isUsable()->whereNotNull('proxy')->get();
            foreach ($servers as $server) {
                $status = get_container_status(server: $server, container_id: $container_name);
                if ($status === 'running') {
                    continue;
                }
                // $server->team->notify(new ProxyStoppedNotification($server));
                resolve(StartProxy::class)($server);
            }
        } catch (\Throwable $th) {
            ray($th->getMessage());
            //throw $th;
        }
    }
}
