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
		ִ��ǰ�ö���, ���ǰ�õĶ������ص���Action, �������ת
		����controller��ִ��return new Action('common/login'); �൱��ֱ����ת
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
			// Action����һ���µ�Action����,��ʾҪ��ת
			$action = $result;
		} elseif ($result === false) {
			// Action����false����Ϊ�ǳ���,������ת��error����
			$action = $this->error;

			$this->error = '';
		} else {
			// ���������, Action���󲻷��ػ��߷���null
			$action = false;
		}

		return $action;
	}
}
