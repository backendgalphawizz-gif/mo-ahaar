<?php

namespace Illuminate\Support\Facades;

interface Auth
{
    /**
     * @return \App\Models\Users|false
     */
    public static function loginUsingId(mixed $id, bool $remember = false);

    /**
     * @return \App\Models\Users|false
     */
    public static function onceUsingId(mixed $id);

    /**
     * @return \App\Models\Users|null
     */
    public static function getUser();

    /**
     * @return \App\Models\Users
     */
    public static function authenticate();

    /**
     * @return \App\Models\Users|null
     */
    public static function user();

    /**
     * @return \App\Models\Users|null
     */
    public static function logoutOtherDevices(string $password);

    /**
     * @return \App\Models\Users
     */
    public static function getLastAttempted();
}