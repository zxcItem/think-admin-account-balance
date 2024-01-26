<?php

namespace app\account\service;

use app\account\model\AccountUser;

/**
 * 用户来源分析
 */
abstract class Source
{
    private static $prov = [
        ['name' => '北京', 'value' => 0],
        ['name' => '天津', 'value' => 0],
        ['name' => '河北', 'value' => 0],
        ['name' => '山西', 'value' => 0],
        ['name' => '内蒙古','value' => 0],
        ['name' => '辽宁', 'value' => 0],
        ['name' => '吉林', 'value' => 0],
        ['name' => '黑龙江','value' => 0],
        ['name' => '上海', 'value' => 0],
        ['name' => '江苏', 'value' => 0],
        ['name' => '浙江', 'value' => 0],
        ['name' => '安徽', 'value' => 0],
        ['name' => '福建', 'value' => 0],
        ['name' => '江西', 'value' => 0],
        ['name' => '山东', 'value' => 0],
        ['name' => '河南', 'value' => 0],
        ['name' => '湖北', 'value' => 0],
        ['name' => '湖南', 'value' => 0],
        ['name' => '广东', 'value' => 0],
        ['name' => '广西', 'value' => 0],
        ['name' => '海南', 'value' => 0],
        ['name' => '重庆', 'value' => 0],
        ['name' => '四川', 'value' => 0],
        ['name' => '贵州', 'value' => 0],
        ['name' => '云南', 'value' => 0],
        ['name' => '西藏', 'value' => 0],
        ['name' => '陕西', 'value' => 0],
        ['name' => '甘肃', 'value' => 0],
        ['name' => '青海', 'value' => 0],
        ['name' => '宁夏', 'value' => 0],
        ['name' => '新疆', 'value' => 0],
        ['name' => '香港', 'value' => 0],
        ['name' => '澳门', 'value' => 0],
        ['name' => '台湾', 'value' => 0]
    ];

    /**
     * 用户来源统计
     * @return array[]
     */
    public static function userToProv(): array
    {
        try {
            self::$prov = array_map(function ($item) {
                $item['value'] = AccountUser::mk()->whereLike('region_prov', "{$item['name']}%")->count();
                return $item;
            }, self::$prov);
            return self::$prov;
        } catch (\Exception $exception) {
            return [];
        }
    }

    /**
     * 用户来源排名
     * @param array $data
     * @return array
     */
    public static function ranking(array $data): array
    {
        try {
            usort($data, function ($a, $b) {
                return $b["value"] - $a["value"];
            });
            return array_slice($data, 0, 10);
        } catch (\Exception $exception) {
            return [];
        }
    }
}