<?php
namespace Privilege\Controller;
use Think\Controller;

class LoginController extends Controller 
{
	public function login()
	{
		if(IS_POST)
		{
			$model = D('AdminMember');
			// 接收表单并验证表单
			// 第二参数说明要使用模型中哪个验证规则
			if($model->create($_POST, 4))
			{
				$ret = $model->login();
				if($ret === TRUE)
				{
					$this->success('登录成功！', U('Layout/Index'));
					exit;
				}
				else 
				{
					if($ret == 2)
						$this->error('密码错误！');
					if($ret == 3)
						$this->error('账号不存在！');
				}
			}
			else 
			{
				$error = $model->getError();
				$this->error($error);
			}
		}
		$this->display();
	}
	public function logout()
	{
		$model = D('AdminMember');
		$model->logout();
		$this->success('已经退出！', U('login'));
	}
	// 生成验证码的图片
	public function check_code_img()
	{
		$Verify = new \Think\Verify();
		$Verify->entry();
	}
}