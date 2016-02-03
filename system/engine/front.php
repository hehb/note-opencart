<?php
final class Front {
	private $registry;
	private $pre_action = array();
	private $error;

	public function __construct($registry) {
		$this->registry = $registry;
	}

	public function addPreAction($pre_action) {
		$this->pre_action[] = $pre_action;
	}

	public function dispatch($action, $error) {
		$this->error = $error;

		/*
		执行前置动作, 如果前置的动作返回的是Action, 则进行跳转
		如在controller中执行return new Action('common/login'); 相当于直接跳转
		*/
		foreach ($this->pre_action as $pre_action) {
			$result = $this->execute($pre_action);

			if ($result) {
				$action = $result;

				break;
			}
		}

		while ($action) {
			$action = $this->execute($action);
		}
	}

	private function execute($action) {
		$result = $action->execute($this->registry);

		if (is_object($result)) {
			// Action返加一个新的Action对象,表示要跳转
			$action = $result;
		} elseif ($result === false) {
			// Action返回false才认为是出错,程序跳转到error处理
			$action = $this->error;

			$this->error = '';
		} else {
			// 正常情况下, Action对象不返回或者返回null
			$action = false;
		}

		return $action;
	}
}
