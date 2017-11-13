/*
* @Author: Administrator
* @Date:   2017-09-09 10:58:26
* @Last Modified by:   Administrator
* @Last Modified time: 2017-09-09 14:44:06
*/
var res = china_city_area_zip;
function province(res) {
	var province_len = res.length;
	var province = '';
	for (var i =0; i < province_len; i++) {
		province += '<option value="'+res[i].id+'">'+res[i].name+'</option>';
	};
	$("#box1").children('select').append(province);
};
function city(res,i){
	var city_len = res[i].child.length;
	var city = '';
	for (var m =0; m < city_len; m++) {
		city += '<option value="'+res[i].child[m].id+'">'+res[i].child[m].name+'</option>';
	}
	$("#box2").children('select').append(city);
}
function area(res,i,m){
	var area_len = res[i].child[m].child.length;
	var area = ''
	for (var j =0; j < area_len; j++) {
		area += '<option value="'+res[i].child[m].child[j].id+'" zipcode="'+res[i].child[m].child[j].zipcode+'">'+res[i].child[m].child[j].name+'</option>';
	}
	$("#box3").children('select').append(area);
}
$("#box1").children('select').on('change',function () {
	var i = $('#box1 select option:selected').index();
	$("#box2").children('select').html('');
	$("#box3").children('select').html('');
	city(res,i);
	var m = 0;
	area(res,i,m);
});
$("#box2").children('select').on('change',function () {
	var i = $('#box1 select option:selected').index();
	var m = $('#box2 select option:selected').index();
	$("#box3").children('select').html('');
	area(res,i,m);
});
var flag
if (flag==1) {
	province(res)
	var province = ''
	var province_len = res.length;

	for (var i =0; i < province_len; i++) {
		if (res[i].name == province_sel) {
			$("#box1").children('select').children('option').eq(i).attr("selected",true);
			city(res,i);
			var city_len = res[i].child.length;
			for (var m =0; m < city_len; m++) {
				if (res[i].child[m].name == city_sel) {
					$("#box2").children('select').children('option').eq(m).attr("selected",true);
					area(res,i,m);
					var area_len = res[i].child[m].child.length;
					for (var j = 0; j < area_len ; j++) {
						if (res[i].child[m].child[j].name == area_sel) {
							$("#box3").children('select').children('option').eq(j).attr("selected",true);
						};
					};
				};
			};
		};
	};
}else{
	var i = 0;
	var m = 0;
	province(res);
	city(res,i);
	area(res,i,m);
}