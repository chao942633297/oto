<?php

namespace app\admin\validate;

use think\Validate;

class GarageValidate extends Validate
{
    protected $rule = [
        ['car_id','require','车系为空'],
        ['brand_id','require','品牌为空'],
        ['manufacturer_id','require','子品牌为空'],
        ['series_id','require','子品牌系列为空'],
        ['displacement','require','排量为空'],
        ['models','require','车型为空'],
        ['year','require','年份为空'],
        ['sort','number','年龄必须是数字'],
    ];
}