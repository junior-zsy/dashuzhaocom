<?php
namespace Privilege\Controller;

class PrivilegeController extends AdminController 
{
	public function add()
	{
		// 先生成privilge表的模型
		// M:当只使用tp自带的方法时，如：select(),add方法等
		// D:如果要用到自己写的东西时需要使用
		$priModel = D('Privilege');
		// 处理表单
		if(IS_POST)
		{
			// 接收表单并根据模型中定义的规则进行验证
			if($priModel->create())
			{
				// 插入数据库
				if($priModel->add() !== FALSE)
				{
					// 添加成功之后页面跳转
					// U函数:生成一个方法的地址：
					// U('lst'):当前控制器中的lst方法
					// U('Abc/lst'):当前模块中的Abc控制器中的lst方法
					// U('Bc/Abc/lst'):当前Bc模块中的Abc控制器中的lst方法
					$this->success('操作成功！', U('lst'));
					exit;
				}
				else 
				{
					// 如果是开发阶段就打印出SQL语句用来调试
					if(APP_DEBUG)
					{
						echo '执行的SQL：'.$priModel->getLastSql();
						exit;
					}
					else 
						$this->error('发生未知错误，请重试！');
				}
			}
			else 
			{
				// 获取验证失败的原因：
				$error = $priModel->getError();
				// 显示信息并跳转页面,如果没有第二个参数代表跳回上一个页面
				$this->error($error);
			}
		}
		// 先取出前两级的权限的树形结构
		$priData = $priModel->getPriTree(1);
		$this->assign('priData', $priData);
		// 显示表单
		$this->display();
	}
	// del方法必须通过get方法传一个id
	public function del($id)
	{
		$priModel = D('Privilege');
		$priModel->delete($id);
		$this->success('操作成功！');
		exit;
	}
	// 批量删除
	public function bdel()
	{
		if(isset($_POST['delid']))
		{
			$priModel = D('Privilege');
			// 把表单提交的数组转化成一个字符串： // 1,2,3,4,5,56
			$id = implode(',', $_POST['delid']);
			$priModel->delete($id);
		}
		$this->success('操作成功！');
		exit;
	}
	// 列表页
	public function lst()
	{
		$priModel = D('Privilege');
		$priData = $priModel->getPriTree(2);
		$this->assign('priData', $priData);
		$this->display();
	}
	// 修改
	public function save($id)
	{
		$priModel = D('Privilege');
		if(IS_POST)
		{
			// 接收表单并验证
			if($priModel->create())
			{
				if($priModel->save() !== FALSE)
				{
					// echo $priModel->getLastSql();
					// exit;
					$this->success('操作成功！', U('lst'));
					exit;
				}
				else 
				{
					// 如果是开发阶段就打印出SQL语句用来调试
					if(APP_DEBUG)
					{
						echo '执行的SQL：'.$priModel->getLastSql();
						exit;
					}
					else 
						$this->error('发生未知错误，请重试！');
				}
			}
			else 
			{
				$error = $priModel->getError();
				$this->error($error);
			}
		}
		// 先取出要修改的记录的原来的数据
		$data = $priModel->find($id);
		$this->assign('data', $data);
		// 先取出前两级的权限的树形结构
		$priData = $priModel->getPriTree(1);
		$this->assign('priData', $priData);
		// 修改表单
		$this->display();
	}
}