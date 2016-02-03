<?php
class Action {
	private $file;
	private $class;
	private $method;
	private $args = array();

	public function __construct($route, $args = array()) {
		$parts = explode('/', str_replace('../', '', (string)$route));
		/*
		对于url为a/b/c/d, 寻找相应的文件
		如果存在
		1. c目录下有个d.php,
		2. b目录下有个c.php, 其中有个d()
		3. a目录下有个b.php, 其中有个c()
		那么以哪个为准呢?
		以1为准, 如果1中的d.php不存在,再找2, 3
		*/
		// Break apart the route
		while ($parts) {
			$file = DIR_APPLICATION . 'controller/' . implode('/', $parts) . '.php';

			if (is_file($file)) {
				$this->file = $file;

				$this->class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', implode('/', $parts));
				break;
			} else {
				$this->method = array_pop($parts);
			}
		}

		if (!$this->method) {
			$this->method = 'index';
		}
		$this->args = $args;
	}

	public function execute($registry) {
		// Stop any magical methods being called
		if (substr($this->method, 0, 2) == '__') {
			return false;
		}

		if (is_file($this->file)) {
			include_once($this->file);

			$class = $this->class;

			$controller = new $class($registry);

			if (is_callable(array($controller, $this->method))) {
				return call_user_func(array($controller, $this->method), $this->args);
			} else {
				return false;
			}
		} else {
			// 返回false表示出错, 比如找不到文件或指定的方法不存在
			return false;
		}
	}
}
