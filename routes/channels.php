<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('server.{serverId}', function ($user) {
    return $user !== null;
});

Broadcast::channel('alerts', function ($user) {
    return $user !== null;
});
