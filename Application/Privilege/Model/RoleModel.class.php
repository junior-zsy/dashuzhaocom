<?php
namespace Privilege\Model;
use Think\Model;

class RoleModel extends Model 
{
	// 第四个参数：0：只有字段存在时验证，1：必须验证 2：只有值不为空时验证
	// 第五个参数：验证的方法，第五个参数和第二个参数要搭配使用, unique:字段值在数据库中是唯一的
	// 第六个参数：1.添加时验证  2：修改时验证 3：两种情况都验证（默认）
	protected $_validate = array(
		array('role_name', 'require', '角色名称不能为空'),
		array('role_name', '', '角色名称已经存在', 1, 'unique'),
		array('pri_id_list', 'chk_pri_id_list', '必须要选择一个权限', 1, 'callback'),
		array('id', '1', '超级管理员角色不允许修改', 1, 'notin', 2),
	);
	protected function chk_pri_id_list($data)
	{
		return isset($_POST['pri_id_list']);
	}
	protected function _before_insert(&$data, $option)
	{
		// 把权限ID的数组转成字符串
		$data['pri_id_list'] = implode(',', $data['pri_id_list']);
	}
	protected function _before_update(&$data, $option)
	{
		// 把权限ID的数组转成字符串
		$data['pri_id_list'] = implode(',', $data['pri_id_list']);
	}
}