<?php

namespace app\admin\validate;

use think\Validate;

class UsersValidate extends Validate
{
    protected $rule = [
        ['phone', 'unique:users', '手机号已经存在']
    ];

}