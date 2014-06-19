<?php
	class s_db {
		
		public $version = "beta 1.1";
		
		private $file_name = "";
		private $table_name = "";
		private $buffer = array();
		
		private function reload_db($file = "_")  //数据库重载
		{
			if ($file != "_")
				$this->file_name = $file;
			if (file_exists($this->file_name))
				$this->buffer = unserialize(file_get_contents($this->file_name));
			else
				file_put_contents($this->file_name, $this->buffer);
		}
		
		private function save_db()  //保存数据库
		{
			if ($this->table_name == "")
				return false;
			file_put_contents($this->file_name, serialize($this->buffer));
			$this->reload_db($this->file_name);
			return true;
		}
		
		public function s_db($file = "_")  //初始化
		{
			if ($file == "_")
				die(false);
			$this->file_name = $file;
			if (file_exists($file))
				$this->buffer = unserialize(file_get_contents($file));
			else
				file_put_contents($file, $this->buffer);
		}
		
		public function select_table($table)  //选择一个数据表
		{
			if (!isset($this->buffer[$table]))
				return false;
			$this->table_name = $table;
			return true;
		}
		
		public function create_table($table, $arr)  //创建一个数据表，参数为表名和索引数组
		{
			if (isset($this->buffer[$table]))
				return false;
			$this->buffer[$table] = array();
			$this->buffer["key_".$table] = $arr;
			$this->save_db();
			$this->select_table($table);
			return true;
		}
		
		public function rename_table($table, $_table)  //修改数据表名
		{
			if (isset($this->buffer[$table]))
				return false;
			$this->buffer[$_table] = $this->buffer[$table];
			$this->buffer["key_".$_table] = $this->buffer["key_".$table];
			$this->delete_table($table);
			$this->save_db();
			$this->select_table($_table);
			return true;
		}
		
		public function copy_table($table, $_table)  //复制数据表
		{
			if (isset($this->buffer[$table]))
				return false;
			$this->buffer[$_table] = $this->buffer[$table];
			$this->buffer["key_".$_table] = $this->buffer["key_".$table];
			$this->save_db();
			$this->select_table($_table);
			return true;
		}
		
		public function delete_table($table)  //删除一个数据表
		{
			unset($this->buffer[$table]);
			unset($this->buffer["key_".$table]);
			$this->save_db();
		}
		
		public function insert_record($arr)  //按照索引顺序插入一条记录
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			$tmp = array();
			for ($i=0;$i<count($this->buffer["key_".$this->table_name]);++$i)
				$tmp[$this->buffer["key_".$this->table_name][$i]] = $arr[$i];
			$this->buffer[$this->table_name][] = $tmp;
			$this->save_db();
			return true;
		}
		
		public function change_record($id, $arr)  //根据ID修改记录
		{
			if ($this->get_record_id($id) == -1)
				return false;
			$this->buffer[$this->table_name][$id] = $arr;
			$this->save_db();
			return true;
		}
		
		public function copy_record($id)  //复制一条记录
		{
			if ($this->get_record_id($id) == -1)
				return false;
			$this->buffer[$this->table_name][] = $this->buffer[$this->table_name][$id];
			$this->save_db();
			return true;
		}
		
		public function delete_record($id)  //根据ID删除一条记录
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			$tmp = array();
			for ($i=0;$i<count($this->buffer[$this->table_name]);++$i)
				if ($i != $id)
					$tmp[] = $this->buffer[$this->table_name][$i];
			$this->buffer[$this->table_name] = $tmp;
			$this->save_db();
			return true;
		}
		
		public function query_db($func)  //条件查询，参数为条件函数，函数参数为一条记录
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			$tmp = array();
			for ($i=0;$i<count($this->buffer[$this->table_name]);++$i)
				if ($func($this->buffer[$this->table_name][$i]))
					$tmp[] = $this->buffer[$this->table_name][$i];
			return $tmp;
		}
		
		public function close_db()  //关闭数据库
		{
			$this->save_db();
			$this->table_name = "";
			$this->file_name = "";
			$this->buffer = array();
			return true;
		}
		
		public function get_record_id($arr)  //获取第一条相同记录的ID
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			for ($i=0;$i<count($this->buffer[$this->table_name]);++$i)
				if ($this->buffer[$this->table_name][$i] == $arr)
					return $i;
			return -1;
		}
		
		public function get_records_id($arr)  //获取所有相同记录的ID
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			$tmp = array();
			for ($i=0;$i<count($this->buffer[$this->table_name]);++$i)
				if ($this->buffer[$this->table_name][$i] == $arr)
					$tmp[] = $i;
			return $tmp;
		}
		
		public function get_all_record()  //获取所有记录
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			return $this->buffer[$this->table_name];
		}
		
		
		public function get_index()  //获取数据表的索引
		{
			if ($this->table_name == "")
				return false;
			if (!isset($this->buffer[$this->table_name]))
				return false;
			return $this->buffer["key_".$this->table_name];
		}	
	}	
?>