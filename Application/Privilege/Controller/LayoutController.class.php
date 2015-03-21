<?php
namespace Privilege\Controller;

class LayoutController extends AdminController 
{
	// 框架集，用来包含其他 三个页面
	public function index()
	{
		$this->display();
	}
	public function top()
	{
		$this->display();
	}
	public function menu()
	{
		$this->display();
	}
	public function main()
	{
		$this->display();
	}
}