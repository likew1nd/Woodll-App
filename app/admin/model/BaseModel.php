<?php
namespace app\admin\model;

use think\Model;
use think\helper\Str;

class BaseModel extends Model
{
    public static function findCompat($data)
    {
        if (empty($data)) {
            return null;
        }
        if (is_array($data)) {
            return static::where($data)->find();
        }
        return static::find($data);
    }

    public static function allCompat($data = null)
    {
        if (is_array($data)) {
            return static::where($data)->select();
        }
        return static::select($data);
    }

    public static function __callStatic($method, $args)
    {
        if (str_starts_with($method, 'getBy')) {
            $field = Str::snake(substr($method, 5));
            return static::where($field, $args[0])->find();
        }
        return parent::__callStatic($method, $args);
    }
}
