<?php

namespace app\admin\validate;

use think\Validate;

class ProjectValidate extends Validate
{
    protected $rule = [
        ['name', 'unique:serviceproject', '服务项目名称存在']
    ];

}