<?php

namespace app\home\model;

use think\Model;

#发短信模型
class PhoneCode extends Model
{
    # 确定链接表名
    protected $table = 'phone_code';

	#验证 验证码是否正确
	public static function checkCode($phone,$code)
	{
		#判断验证码和手机号
		$one = self::where('phone',$phone)->order('created_at','desc')->find();

		if (empty($one) || $code != $one['code']) {
			return false;
		}
		return true;
	}
}