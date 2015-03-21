<?php
namespace Privilege\Model;
use Think\Model;

class PrivilegeModel extends Model 
{
	protected $_validate = array(
		array('pri_name', 'require', '权限名称不能为空'),
		array('module_name', 'require', '模块名称不能为空'),
		array('controller_name', 'require', '控制器名称不能为空'),
		array('action_name', 'require', '方法名称不能为空'),
	);
	// 获取树形结构的数据
	public function getPriTree($pri_level = 2)
	{
		$sql = 'SELECT * FROM sh_privilege WHERE pri_level <= '.$pri_level.' ORDER BY CONCAT(pri_path,"-",id) ASC';
		return $this->query($sql);
	}
	// 加一个前置钩子函数，这个函数会在add之前自动调用
	// $data : 表单中的数据的数组
	protected function _before_insert(&$data, $option)
	{
		// 如果不是顶级权限那么就计算出当前权限的path,level的值
		if($data['parent_id'] != 0)
		{
			// 1. 先取出上级权限的level和path
			$sql = 'SELECT pri_level,pri_path FROM sh_privilege WHERE id = '.$data['parent_id'];
			$pri = $this->query($sql);
			// 2. 计算两个值并放到模型数据中一并插入到数据库中
			$data['pri_level'] = $pri[0]['pri_level'] + 1;
			$data['pri_path'] = $pri[0]['pri_path'] .'-'. $data['parent_id'];
		}
	}
	// 添加一个前置的个性的钩子函数用来计算level和path字段
	protected function _before_update(&$data, $option)
	{
		// 要修改记录的id
		$_id = $option['where']['id'];
		if($data['parent_id'] == 0)
		{
			$data['pri_level'] = 0;
			$data['pri_path'] = 0;
		}
		else 
		{
			// 1. 先取出上级权限的level和path
			$sql = 'SELECT pri_level,pri_path FROM sh_privilege WHERE id = '.$data['parent_id'];
			$pri = $this->query($sql);
			// 2. 计算两个值并放到模型数据中一并插入到数据库中
			$data['pri_level'] = $pri[0]['pri_level'] + 1;
			$data['pri_path'] = $pri[0]['pri_path'] .'-'. $data['parent_id'];
		}
		// 修改所有子权限的level和path时
		// 找出子权限
		$sql = 'SELECT GROUP_CONCAT(id) did FROM sh_privilege WHERE CONCAT("-",pri_path,"-") LIKE "%-'.$_id.'-%"';
		$did = $this->query($sql);
		$did = $did[0]['did'];  //1,2,3,4,5,6
		// 计算新的level
		// 先取出当前修改的权限修改前的level值
		$old_data = $this->query('SELECT pri_level,pri_path FROM sh_privilege WHERE id = '.$_id);
		$old_data = $old_data[0];
		// 计算level的差
		$dlevel = $data['pri_level'] - $old_data['pri_level'];
		$oldpath = $old_data['pri_path'].'-'.$_id;
		$newpath = $data['pri_path'] .'-'. $_id;
		// 修改所有子权限的level和path
		$this->execute("UPDATE sh_privilege SET pri_level=pri_level+$dlevel,pri_path=replace(pri_path,'$oldpath','$newpath') WHERE id IN($did)");
	}
	// 添加一个删除前的钩子
	protected function _before_delete($option)
	{
		if(isset($option['where']['id']))
		{
			// 批量删除
			if(is_array($option['where']['id']))
			{
				// 循环每一个权限取出子权限并删除
				$id = explode(',', $option['where']['id'][1]);
				foreach ($id as $k => $v)
				{
					// 找出子权限
					$sql = 'SELECT GROUP_CONCAT(id) did FROM sh_privilege WHERE CONCAT("-",pri_path,"-") LIKE "%-'.$v.'-%"';
					$did = $this->query($sql);
					// 如果有子权限就删除
					if($did)
					{
						$this->execute('DELETE FROM sh_privilege WHERE id IN('.$did[0]['did'].')');
					}
				}
			}
			// 单个删除
			else 
			{
				// 先取出子权限的ID并构造一个字符串：1,2,4,5,6
				$sql = 'SELECT GROUP_CONCAT(id) did FROM sh_privilege WHERE CONCAT("-",pri_path,"-") LIKE "%-'.$option['where']['id'].'-%"';
				$did = $this->query($sql);
				if($did)
				{
					$this->execute('DELETE FROM sh_privilege WHERE id IN('.$did[0]['did'].')');
				}
			}
		}
	}
}













