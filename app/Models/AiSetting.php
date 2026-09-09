<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiSetting extends Model
{
    protected $table = 'ai_settings';
    protected $fillable = ['key_name', 'key_value', 'description'];

    public static function get($key, $default = null)
    {
        $setting = static::where('key_name', $key)->first();
        return $setting && $setting->key_value !== null ? $setting->key_value : $default;
    }

    public static function set($key, $value, $description = null)
    {
        $data = ['key_value' => $value];
        if ($description !== null) {
            $data['description'] = $description;
        }
        return static::updateOrCreate(['key_name' => $key], $data);
    }

    public static function getAllSettings()
    {
        return static::pluck('key_value', 'key_name')->toArray();
    }

    public static function isCopilotEnabled(): bool
    {
        return static::get('copilot_enabled', 'Y') === 'Y';
    }
}
