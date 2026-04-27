<?php

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email'    => ['required', 'email'],
        'password' => ['required'],
    ]);

    if (! Auth::attempt($credentials)) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    $request->session()->regenerate();

    return response()->json(['user' => Auth::user()]);
});

Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return response()->json(['message' => 'Logged out']);
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/servers', function () {
        return Server::where('is_active', true)
            ->with('latestMetric')
            ->orderBy('name')
            ->get()
            ->map(fn ($server) => [
                'id'          => $server->id,
                'name'        => $server->name,
                'hostname'    => $server->hostname,
                'ip_address'  => $server->ip_address,
                'environment' => $server->environment,
                'status'      => $server->status,
                'is_active'   => $server->is_active,
                'metric'      => $server->latestMetric ? [
                    'cpu_usage'     => round($server->latestMetric->cpu_usage, 1),
                    'memory_usage'  => round($server->latestMetric->memory_usage, 1),
                    'memory_used'   => $server->latestMetric->memory_used,
                    'memory_total'  => $server->latestMetric->memory_total,
                    'disk_usage'    => round($server->latestMetric->disk_usage, 1),
                    'disk_used'     => $server->latestMetric->disk_used,
                    'disk_total'    => $server->latestMetric->disk_total,
                    'network_in'    => round($server->latestMetric->network_in, 2),
                    'network_out'   => round($server->latestMetric->network_out, 2),
                    'request_rate'  => round($server->latestMetric->request_rate, 1),
                    'response_time' => round($server->latestMetric->response_time, 0),
                ] : null,
            ]);
    });

    Route::get('/alerts/unread', function () {
        return \App\Models\Alert::where('is_read', false)
            ->with('server:id,name')
            ->latest('triggered_at')
            ->limit(20)
            ->get();
    });
});
