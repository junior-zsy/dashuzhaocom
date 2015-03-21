<?php
namespace Privilege\Model;
use Think\Model;

// 添加管理员，修改管理员，登录时
class AdminMemberModel extends Model 
{
	protected $_validate = array(
		array('admin_name', 'require', '管理员名称不能为空'),
		array('admin_pass', 'require', '密码不能为空', 1, 'regex', 1),
		/************ 以下四项在登录时不会验证 ******************/
			// 当前这个值不为空时验证
			array('radmin_pass', 'admin_pass', '两次密码输入不一致', 2, 'confirm'),
			// 当有role_id字段时验证
			array('role_id', 'require', '角色的id不能为空'),
			// 添加和修改时验证
			array('admin_name', '', '管理员名称已经存在！', 1, 'unique', 1),
			array('admin_name', '', '管理员名称已经存在！', 1, 'unique', 2),
		// 登录时的规则
		array('admin_pass', 'require', '密码不能为空', 1, 'regex', 4),
		array('chk_code', 'chk_code', '验证码不正确！', 1, 'callback', 4),
	);
	// 验证验证码是否正确的
	protected function chk_code($data)
	{
		 $verify = new \Think\Verify();
		 return $verify->check($data);
	}
	protected function _before_insert(&$data, $option)
	{
		// 生成六位的密钥
		$salt = substr(uniqid(), -6);
		$pass = md5(md5($data['admin_pass']) . $salt);
		$data['salt'] = $salt;
		$data['admin_pass'] = $pass;
	}
	protected function _before_update(&$data, $option)
	{
		// 如果密码为空就不修改密码
		if(!$data['admin_pass'])
			unset($data['admin_pass']);
		else 
		{
			// 生成六位的密钥
			$salt = substr(uniqid(), -6);
			$pass = md5(md5($data['admin_pass']) . $salt);
			$data['salt'] = $salt;
			$data['admin_pass'] = $pass;
		}
	}
	private function _loadPriDataToSession($role_id)
	{
		if($role_id != 1)
		{
			// 根据角色ID取出所拥有的权限的id
			$data = $this->query('SELECT pri_id_list FROM sh_role WHERE id = '.$role_id);
			$data = $data[0]['pri_id_list'];  // 1,2,3,4,5,6
			// 根据权限ID取出权限的信息
			$priData = $this->query("SELECT * FROM sh_privilege WHERE id IN($data) ORDER BY CONCAT(pri_path,'-',id) ASC");
		}
		else 
		{
			// 如果是超级管理员那么就取出所有的权限
			$priData = $this->query('SELECT * FROM sh_privilege ORDER BY CONCAT(pri_path,"-",id) ASC');
		}
		// 把用户可以访问的方法都存到session中
		$_url = array();
		$_menu = array();
		$_menu1 = array();
		foreach ($priData as $k => $v)
		{
			$_url[] = $v['module_name'].'/'.$v['controller_name'].'/'.$v['action_name'];
			// 取出前两级的权限
			if($v['pri_level'] <= 1)
				$_menu[$v['parent_id']][] = $v;
		}
		session('url', $_url);
		session('menu', $_menu);
	}
	// 登录
	public function login()
	{
		// 注意在find方法之前$this->admin_pass是表单中的密码，但find方法之后$this->admin_pass就是数据库中的密码了，所以在验证密码时用的是$_POST['admin_pass']表单中的密码而不是$this->admin_pass
		//echo $this->admin_pass; // 表单提交过来的密码
		// 先检查用户的账号是否正确
		$user = $this->where("admin_name='$this->admin_name'")->find();
		if($user)
		{
			//echo$this->admin_pass; // 从数据库中刚刚取出来的密码
			// 验证密码是否正确
			if(md5(md5($_POST['admin_pass']) . $user['salt']) == $user['admin_pass'])
			{
				// 登录成功之后把用户的信息存到session
				session('uid', $user['id']);
				session('uname', $user['admin_name']);
				// 取出这个管理员的权限并存到session中
				$this->_loadPriDataToSession($user['role_id']);
				return TRUE;
			}
			else 
				return 2;
		}
		else 
			return 3;  // 用户名不存在
	}
	public function logout()
	{
		session(null);
	}
}