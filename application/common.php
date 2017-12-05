<?php
// 应用公共文件
use think\Session;

/**
 * 生成二维码
 * @param int $param 
 * @param $level 容错等级
 * @param $size 图片大小
 * @return 
 */
function qrcode($param,$level=3,$size=4){
	vendor('phpqrcode.phpqrcode');
	$url = "./uploads/qrcode/$param.png";
	$errorCorrectionLevel =intval($level) ;//容错级别 
	$matrixPointSize = intval($size);//生成图片大小 
	//生成二维码图片 
    $to_url = ADMIN_URL."/home/wechat/BrowserType?param=".$param;
	$object = new \QRcode();
	$img = $object->png($to_url,$url, $errorCorrectionLevel, $matrixPointSize, 2,false);
}

/**
 * 生成二维码
 * @param int $param 
 * @param $level 容错等级
 * @param $size 图片大小
 * @return 
 */
function ShopQrcode($param,$level=3,$size=4){
    vendor('phpqrcode.phpqrcode');
    $url = "./uploads/receivables/$param.png";
    $errorCorrectionLevel =intval($level) ;//容错级别 
    $matrixPointSize = intval($size);//生成图片大小 
    //生成二维码图片 
    $data = "{\"code\":\"oto\",\"data\":\"$param\"}";
    $object = new \QRcode();
    $img = $object->png($data,$url, $errorCorrectionLevel, $matrixPointSize, 2,false);
}
/**
 * 把数据写入一个文件
 *
 * @param string          $file    文件名
 * @param array|generator $data    数据，可以被 foreach 遍历的数据，数组或者生成器
 * @param string          $tplFile 模板文件，以哪个模板填写数据，如果不提供则生成空白 xlsx 文件
 * @param int             $skipRow 跳过表头的行数，默认为 1
 */
function putExcel($file, $data, $tplFile = null, $skipRow = 1)
{
    Vendor("PHPExcel.PHPExcel");
    if ($tplFile) {
        if (file_exists($tplFile)) {
            $objReader = PHPExcel_IOFactory::createReader('Excel2007');
            $objPHPExcel = $objReader->load($tplFile);
        } else {
            throw new \Exception("File `{$tplFile}` not exists");
        }
    } else {
        $objPHPExcel = new \PHPExcel();
    }

    $objPHPExcel->setActiveSheetIndex(0);
    $objSheet=$objPHPExcel->getActiveSheet();
    $objSheet->setTitle('export');

    $rowNum = 1;
    foreach ($data as $row) {
        $colNum = 0;
        foreach ($row as $val) {
            $objSheet->setCellValueByColumnAndRow(
                $colNum,
                $rowNum + $skipRow,
                $val
            );
            ++$colNum;
        }
        ++$rowNum;
    }

    PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007')->save($file);
}

function is_weixin(){
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ) {
        return true;
    }
    return false;
}

/**
 * 从 Excel 获取所有行
 *
 * @param string   $file          xlsx 文件路径
 * @param int|null $highestColumn 列数，为 null 时候自动检测
 * @param array    $skipRows      跳过的行，默认跳过第一行（表头）
 * @param bool     $skipBlankRow  是否跳过空白行，默认为 true
 *
 * @return generator 可遍历的生成器
 */
function writeExcel($file, $highestColumn = null, $skipRows = [1], $skipBlankRow = true)
{

    vendor("PHPExcel.PHPExcel");

    $objReader = PHPExcel_IOFactory::createReader('Excel2007');

    $objPHPExcel = $objReader->load($file);

    $sheet = $objPHPExcel->getSheet(0);
    $highestRow = $sheet->getHighestRow();

    is_null($highestColumn) and $highestColumn = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());

    for ($row = 1; $row <= $highestRow; ++$row) {

        if (in_array($row, $skipRows)) {

            continue;
        }

        $rowData = [];
        for ($col = 0; $col < $highestColumn; $col++) {

            $value = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($col, $row)->getValue();
            $rowData[] = is_null($value) ? '' : $value;

        }

        if ($skipBlankRow) {

            if (!array_filter($rowData)) {

                continue;
            }
        }

        yield $rowData;
    }

}

/**
 *
 * 导出Excel -- 例子
 * @param $data->二维数组
 */
function put_excel($filename,$data){//导出Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$filename.'.xlsx');
    header('Cache-Control: max-age=0');
    # 默认跳过第一行表头
    putExcel('php://output', $data);
    exit();
}


//随机生成唯一订单号
function order_sn(){
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    do{
        $order_num = (String) $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') .time(). substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
    }while(\think\Db::table('order')->where('pay_order_num',$order_num)->count() > 0);
    return $order_num;
}

//令牌
function _token(){
    $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
    $encrypt_key = md5(((float) date("YmdHis") + rand(100,time())).rand(1000,9999).$yCode[intval(date('Y')) - 2011]);
    $token = md5(md5(substr(time(), 0, 3).$encrypt_key));
    return $token;
}
/**
*根据某字段对多维数组进行排序
*@param $array  要排序的数组
*@param $field  要根据的数组下标
*@return void
*/
function sortArrByField(&$array, $field, $desc = false){
  $fieldArr = array();
  foreach ($array as $k => $v) {
    $fieldArr[$k] = $v[$field];
  }
  $sort = $desc == false ? SORT_ASC : SORT_DESC;
  array_multisort($fieldArr, $sort, $array);
}
//转码
function input_csv($handle){
    $out = array ();
    $n = 0;
    while ($data = fgetcsv($handle, 10000)){
        $num = count($data);
        for ($i = 0; $i < $num; $i++){
            $out[$n][$i] = $data[$i];
        }
        $n++;
    }
    return $out;
}
//转换格式
function transformation($kv){//转码
    $encode = mb_detect_encoding($kv, array('ASCII','UTF-8','GB2312','GBK','BIG5'));
    if($encode!='UTF-8'){
        $kv = iconv ($encode, 'utf-8', $kv);
    }
    return $kv;
}


/**
*查询要查询用户指定级别内的所有下级id
*$uid:要查询用户集合
*$class:要查询的级别
*$userall:静态变量占位
*$users:用户集合
*return----查询指定用户的指定级别内的所有下级id集合(包括自己)
*/
function getChildenAll_class($uid,$users,$userall = '',$class=''){
    if(empty($userall)){
        static $userall = [];
    }else{
        static $userall = [];
        $userall = [];
    }
    if(!in_array($uid, $userall)) {
        if(is_array($uid)){
            foreach($uid as $v){
                $userall[] = $v;
            }
        }else{
            array_push($userall, $uid);
        }
    }
    $userChildren = [];
    foreach($users as $k=>$v){
        if(is_array($uid)){
            if(in_array($v['pid'],$uid)){
                array_push($userChildren,$v['id']);
            } 
        }else{
            if($v['pid'] == $uid){
                array_push($userChildren,$v['id']);
            } 
        }
    }
    $userall = array_unique(array_merge($userall, $userChildren));
    if(!empty($userChildren)){
        if($class){
            $class--;
            if($class > 0){
                getChildenAll_class($userChildren,$users,'',$class);
            }       
        }else{
            getChildenAll_class($userChildren,$users);
        }
    }
    sort($userall);

    // dump($userall);
    return $userall;
}

/**
 * 获取指定级别下级
 * @param $uid char 要查询下级的用户集合id；如[1,2,3]
 * @param $num int   要查询的级别
 * @return 查询级别的用户下级
 */
function getChilden($uid,$num = 1){
    $user1 = db('users')->where('pid','in',$uid)->field('id,pid')->select();

    $users_id = [];
    foreach($user1 as $k=>$v){
        $users_id[] = $v['id'];
    }
    for($i = 1;$i < $num;$i++){
        if(!$users_id){
            return $users_id;
        }
        $users_id = getChilden($users_id,$num-1);
        return $users_id;
    }
    return $users_id;
}


#调用百度地图API获取商店与用户两点之间的驾车距离
#单位 m
function shopDistance($userlng,$userlat,$lng,$lat)
{   
    if ($userlng <=0 && $userlat <= 0) {
        return 0;
    }
    $geturl = "http://api.map.baidu.com/routematrix/v2/driving?output=json&origins=".$userlat.",".$userlng."&destinations=".$lat.",".$lng."&ak=IhqCLOzr9WpxstrVrWDGQcDGihq0jzi5";  
    $address_data = file_get_contents($geturl);
    $json_data = json_decode($address_data);

    $distance=$json_data->result[0]->distance->value; 
    return $distance;
}


/*================================新增=======================================*/

//获取用户上级
function getUpUser($arr,$prentId){
    $totalNum = [];
    while($prentId !== 0){
        foreach($arr as $key=>$val){
            if($val['user_id'] == $prentId){
                array_push($totalNum,$val['user_id']);
                $prentId = $val['cid'];
                break;
            }
        }
    }
    return $totalNum;
}


//获取所有下级
function getSubtree($arr,$parent=0){
    $task = array($parent);//创建任务表
    $subs = array();//存子孙栏目的数组
    while(!empty($task))//如果任务表不为空 就表示要做任务
    {
        $flag = false;//默认没找到子树
        foreach($arr as $k=>$v){
            if($v->pid == $parent){
                $subs [] = $v->id;
                array_push($task,$v->id);//借助栈 把新的地区的id压入栈
                $parent = $v->id;
                unset($arr[$k]);//把找到的单元unset掉
                $flag = true;
            }
        }
        if(!$flag){//表示没找到子树
            array_pop($task);
            $parent = end($task);
        }
    }
    return $subs;
}

