<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ai_settings';
    protected $fillable = ['key_name', 'key_value', 'description'];

    public static function get($key, $default = null)
    {
        try {
            $setting = static::where('key_name', $key)->first();
            return $setting && $setting->key_value !== null ? $setting->key_value : $default;
        } catch (\Throwable $e) {
            return $default;
        }
    }

    public static function set($key, $value, $description = null)
    {
        try {
            $data = ['key_value' => $value];
            if ($description !== null) {
                $data['description'] = $description;
            }
            return static::updateOrCreate(['key_name' => $key], $data);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function getAllSettings()
    {
        try {
            return static::pluck('key_value', 'key_name')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function isCopilotEnabled(): bool
    {
        try {
            return static::get('copilot_enabled', 'Y') === 'Y';
        } catch (\Throwable $e) {
            return false;
        }
    }
}
