<?php
namespace Privilege\Controller;
use Think\Controller;

// 继承自这个控制器的控制器都必须先登录
class AdminController extends Controller 
{
	public function __construct()
	{
		// 如果有父类必须先调用父类的构造函数
		parent::__construct();
		// 验证登录
		if(!session('uid'))
			redirect(U('Privilege/Login/login'));
		// 判断用户是否有权限访问
		$url = session('url');
		if(MODULE_NAME .'/'. CONTROLLER_NAME == 'Privilege/Layout')
			return TRUE;
		if(!in_array(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME, $url))
			$this->error('无权访问！');
	}
}