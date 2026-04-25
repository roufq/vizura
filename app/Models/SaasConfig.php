<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SaasConfig extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get($key, $default = null)
    {
        return Cache::rememberForever("saas_config_{$key}", function () use ($key, $default) {
            $config = static::where('key', $key)->first();
            return $config ? $config->value : $default;
        });
    }

    public static function set($key, $value)
    {
        $config = static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("saas_config_{$key}");
        return $config;
    }
}
