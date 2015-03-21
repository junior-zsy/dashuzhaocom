<?php
namespace Privilege\Controller;

class RoleController extends AdminController 
{
	public function add()
	{
		if(IS_POST)
		{
			$model = D('Role');
			if($model->create())
			{
				if($model->add() !== FALSE)
				{
					$this->success('操作成功！', U('lst'));
					exit;
				}
				else 
				{
					if(APP_DEBUG)
					{
						echo '执行的SQL：'.$model->getLastSql();
						exit;
					}
					else 
						$this->error('发生未知错误，请重试！');
				}
			}
			else 
			{
				$error = $model->getError();
				$this->error($error);
			}
		}
		// 先取出所有的权限让用户选
		$priModel = D('Privilege');
		// 获取权限的权限的树形数据
		$priData = $priModel->getPriTree();
		$this->assign('priData', $priData);
		// 显示表单
		$this->display();
	}
	// del方法必须通过get方法传一个id
	public function del($id)
	{
		if($id != 1)
		{
			$model = D('Role');
			$model->delete($id);
		}
		$this->success('操作成功！');
		exit;
	}
	// 批量删除
	public function bdel()
	{
		if(isset($_POST['delid']))
		{
			// 查找数组中有没有1,如果有就从数组中把1删除掉
			$key = array_search(1, $_POST['delid']);
			if($key !== FALSE)
				unset($_POST['delid'][$key]);  // 从数组中把1删除掉
			// 数组不为空时执行删除
			if($_POST['delid'])
			{
				$model = D('Role');
				// 把表单提交的数组转化成一个字符串： // 1,2,3,4,5,56
				$id = implode(',', $_POST['delid']);
				$model->delete($id);
			}
		}
		$this->success('操作成功！');
		exit;
	}
	// 列表页
	public function lst()
	{
		$model = M('Role');
		// 取出总的记录数
		$count = $model->count();
		// 生成翻页对象
		$page = new \Think\Page($count, 15);
		// 生成翻页字符串
		$pageStr = $page->show();
		$this->assign('pageStr', $pageStr);
		// 取这一页的记录
		$data = $model->field('a.*,GROUP_CONCAT(b.pri_name) pri_name')->alias('a')->join('LEFT JOIN sh_privilege b ON FIND_IN_SET(b.id,a.pri_id_list)')->limit($page->firstRow, $page->listRows)->group('a.id')->select();
		$this->assign('data', $data);
		$this->display();
	}
	// 修改
	public function save($id)
	{
		$model = D('Role');
		if(IS_POST)
		{
			if($model->create())
			{
				if($model->save() !== FALSE)
				{
					$this->success('操作成功！', U('lst'));
					exit;
				}
				else 
				{
					if(APP_DEBUG)
					{
						echo '执行的SQL：'.$model->getLastSql();
						exit;
					}
					else 
						$this->error('发生未知错误，请重试！');
				}
			}
			else 
			{
				$error = $model->getError();
				$this->error($error);
			}
		}
		// 先取出要修改的记录
		$data = $model->find($id);
		$this->assign('data', $data);
		// 获取权限的权限的树形数据
		$priModel = D('Privilege');
		$priData = $priModel->getPriTree();
		$this->assign('priData', $priData);
		$this->display();
	}
}