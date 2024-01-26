<?php


declare (strict_types=1);

namespace app\account\model;

use think\admin\Model;

/**
 * 模型抽象类
 * @class Abs
 * @package app\account\model
 */
abstract class Abs extends Model
{

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getCreateTimeAttr($value): string
    {
        return format_datetime($value);
    }

    /**
     * 格式化输出时间
     * @param mixed $value
     * @return string
     */
    public function getUpdateTimeAttr($value): string
    {
        return format_datetime($value);
    }

    /**
     * 时间写入格式化
     * @param mixed $value
     * @return string
     */
    public function setCreateTimeAttr($value): string
    {
        return is_string($value) ? str_replace(['年', '月', '日'], ['-', '-', ''], $value) : $value;
    }

    /**
     * 时间写入格式化
     * @param mixed $value
     * @return string
     */
    public function setUpdateTimeAttr($value): string
    {
        return $this->setCreateTimeAttr($value);
    }

    /**
     * 字段属性处理
     * @param mixed $value
     * @return string
     */
    public function setExtraAttr($value): string
    {
        return is_string($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 字段属性处理
     * @param mixed $value
     * @return array
     */
    public function getExtraAttr($value): array
    {
        return empty($value) ? [] : (is_string($value) ? json_decode($value, true) : $value);
    }
}