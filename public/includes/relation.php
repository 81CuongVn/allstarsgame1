<?php
	class Relation {
		protected static	$_fields	= array();
		protected			$_values	= array();
		protected static	$_table		= array();

		static				$paranoid		= false;
		static				$always_cached	= false;
		static				$password_field	= false;

		private				$changed_properties	= array();

		private	$_class_storage	= array();

		function is_stored($key) {
			if(isset($this->_class_storage[$key])) {
				return true;
			} else {
				return false;
			}
		}

		function get_stored_object($key) {
			return $this->_class_storage[$key];
		}

		function store_object($key, $data) {
			$this->_class_storage[$key]		= $data;

			return $data;
		}

		static function initialize($table = '') {
			$class		= get_called_class();
			$table		= $table ? $table : strtolower(Inflector::pluralize($class));

			$fields		= Recordset::query('SHOW FIELDS FROM `' . $table . '`', true);
			$field_list	= array();

			foreach($fields->result_array() as $field) {
				$field_list[$field['Field']]	= $field['Field'];
			}

			$class::$_fields[$class]	= $field_list;
			$class::$_table[$class]		= $table;
		}

		static function find($conditions, $options = array()) {
			$class				= get_called_class();
			$output				= array();
			$cache				= false;
			$skip_after_assign	= false;

			if(is_bool($options)) {
				$count_only	= $options;
			} else {
				$count_only	= false;
			}

			if(is_numeric($conditions)) {
				if(isset($options['cache'])) {
					$cache	= $options['cache'];
				} else {
					$cache	= $class::$always_cached ? true : $cache;
				}

				if(isset($options['skip_after_assign'])) {
					$skip_after_assign	= $options['skip_after_assign'];
				}

				$item	= Recordset::query('SELECT * FROM ' . $class::$_table[$class] . ' WHERE id=' . $conditions, $cache);

				if($item->num_rows) {
					$instance	= new $class();
					$instance->assign($item->row_array());

					if(method_exists($instance, 'after_assign')) {
						$instance->after_assign();
					}

					return $instance;
				} else {
					return false;
				}
			} else {
				if($class::$paranoid) {
					if($conditions) {
						$conditions	.= ' AND removed=0';
					} else {
						$conditions	.= 'removed=0';
					}
				}

				if(isset($options['cache'])) {
					$cache	= $options['cache'];
				}

				if(isset($options['skip_after_assign'])) {
					$skip_after_assign	= $options['skip_after_assign'];
				}

				if($count_only) {
					$items	= Recordset::query('SELECT id FROM ' . $class::$_table[$class] . ' WHERE ' . $conditions, $cache);

					return $items->num_rows;
				} else {
					if(isset($options['reorder'])) {
						$conditions	.= ' ORDER BY ' . $options['reorder'];
					}

					if(isset($options['limit'])) {
						$conditions	.= ' LIMIT ' . $options['limit'];
					}

					$items	= Recordset::query('SELECT * FROM ' . $class::$_table[$class] . ' WHERE ' . $conditions, $cache);
				}

				foreach($items->result_array() as $item) {
					$instance	= new $class();
					$instance->assign($item);

					if(method_exists($instance, 'after_assign') && !$skip_after_assign) {
						$instance->after_assign();
					}

					$output[]	= $instance;
				}

				return $output;
			}
		}

		static function find_first($conditions, $options = array()) {
			$class	= get_called_class();
			$result	= $class::find($conditions, $options);

			if(is_array($result)) {
				return sizeof($result) ? $result[0] : false;				
			} else {
				return $result ? $result : false;
			}
		}

		static function find_last($conditions) {
			$class	= get_called_class();
			$result	= $class::find($conditions);

			if(is_array($result)) {
				return sizeof($result) ? $result[sizeof($result) - 1] : false;
			} else {
				return $result ? $result : false;
			}
		}

		function __get($property) {
			$class	= get_class($this);

			if(!isset($class::$_fields[$class][$property])) {
				throw new Exception('Property not found ' . $class . '::' . $property);
			} else {
				if(method_exists($this, 'before_get')) {
					$this->before_get($property);
				}

				if(!isset($this->_values[$property])) {
					return NULL;
				} else {
					return $this->_values[$property];					
				}
			}
		}

		function __set($property, $value) {
			$class	= get_class($this);

			if(!isset($class::$_fields[$class][$property])) {
				throw new Exception('Property not found ' . $class . '::' . $property);
			}

			if(!isset($this->_values[$property]) || (isset($this->_values[$property]) && $value != $this->_values[$property])) {
				$old_value	= isset($this->_values[$property]) ? $this->_values[$property] : null;

				if(method_exists($this, 'before_set')) {
					$this->before_set($property, $value, $old_value);
				}

				$this->_values[$property]				= $value;
				$this->changed_properties[$property]	= true;

				if(method_exists($this, 'after_set')) {
					$this->after_set($property, $value, $old_value);
				}
			}
		}

		private function assign($values) {
			$this->_values	= $values;
		}

		private function create() {
			$class	= get_class($this);
			$insert	= array();

			foreach($this->_values as $_ => $value) {
				if($_ == 'id' || !(isset($this->changed_properties[$_]) && $this->changed_properties[$_])) {
					continue;
				}

				if($class::$password_field && $class::$password_field == $_) {
					if($this->new_record()) {
						$insert[$_]	= array('escape' => false, 'value' => password($value));
					} else {
						$insert[$_]	= $value;						
					}
				} else {
					$insert[$_]	= $value;
				}
			}

			if(isset($class::$_fields[$class]['created_at'])) {
				$insert['created_at']	= array('escape' => false, 'value' => 'NOW()');
			}

			if(isset($class::$_fields[$class]['updated_at'])) {
				$insert['updated_at']	= array('escape' => false, 'value' => 'NOW()');
			}

			if(method_exists($this, 'before_create')) {
				$this->before_create();
			}

			$this->_values['id']	= Recordset::insert($class::$_table[$class], $insert);

			if(method_exists($this, 'after_create')) {
				$this->after_create();
			}

			return true;
		}

		private function update() {
			$class	= get_class($this);
			$update	= array();

			if(method_exists($this, 'before_update')) {
				$this->before_update();
			}

			foreach($this->_values as $_ => $value) {
				if($_ == 'id') {
					continue;
				}

				if($class::$password_field && $class::$password_field == $_ && isset($this->changed_properties[$_])) {
					$update[$_]	= ['escape' => false, 'value' => password($value)];
				} elseif(isset($this->changed_properties[$_]) && $this->changed_properties[$_]) {
					$update[$_]	= $value;
				}
			}

			if(!sizeof($update)) {
				return;
			}

			if(isset($class::$_fields[$class]['updated_at'])) {
				$update['updated_at']	= array('escape' => false, 'value' => 'NOW()');
			}

			Recordset::update($class::$_table[$class], $update, array('id' => $this->_values['id']));

			if(method_exists($this, 'after_update')) {
				$this->after_update();
			}
		}

		function __isset($field) {
			$class = get_called_class();
			return isset($class::$_fields[$class][$field]);
		}

		function new_record() {
			return !isset($this->_values['id']);
		}

		function save() {
			if(!$this->new_record()) {
				return $this->update();
			} else {
				return $this->create();
			}
		}

		static function all($options = []) {
			$class	= get_called_class();
			
			/*
			$items	= Recordset::query('SELECT id FROM ' . $class::$_table[$class]);
			$result	= array();

			foreach($items->result_array() as $item) {
				$result[]	= $class::find($item['id'], $conditions);
			}
			*/

			return $class::find('1=1', $options);
		}

		static function random() {
			$class	= get_called_class();

			return $class::find_first('1=1', array('reorder' => 'RAND()'));
		}

		static function includes($id) {
			$class	= get_called_class();			
			
			return Recordset::query('SELECT id FROM ' . $class::$_table[$class] . ' WHERE id="' . addslashes($id) . '"')->num_rows;
		}

		function destroy() {
			$class	= get_class($this);

			if(method_exists($this, 'before_destroy')) {
				$this->before_destroy();
			}

			if(isset($class::$paranoid) && $class::$paranoid) {
				$this->removed	= 1;

				Recordset::update($class::$_table[$class], array(
					'removed'	=> 1
				), array(
					'id'	=> $this->id
				));
			} else {
				Recordset::delete($class::$_table[$class], array(
					'id'	=> $this->id
				));
			}

			if(method_exists($this, 'after_destroy')) {
				$this->after_destroy();
			}
		}

		function get_fields() {
			$class	= get_class($this);

			return Relation::$_fields[$class];
		}

		public static function __callStatic($name, $args) {
			$class				= get_called_class();
			$words				= ['_and_', '_or_'];
			$is_finder_mmethod	= false;

			if(strpos($name, 'find_by_') === 0) {
				$is_finder_mmethod	= 1;
			}

			if(strpos($name, 'find_all_by_') === 0) {
				$is_finder_mmethod	= 2;
			}

			if($is_finder_mmethod) {
				$sql			= '';
				$have_words		= false;
				$expected_args	= 1;

				if($is_finder_mmethod == 1) {
					$string	= substr($name, 8);
				} elseif($is_finder_mmethod == 2) {
					$string	= substr($name, 12);
				}

				foreach($words as $word) {
					if(strpos($name, $word) !== false) {

					}
				}

				if(!$have_words) {
					if(!sizeof($args)) {
						throw new Exception("Argument Error. Missing an argument");
					} else {
						if($is_finder_mmethod == 2 && is_array($args)) {
							$args_data	= [];

							foreach ($args as $arg) {
								$args_data[]	= "'" . addslashes($arg) . "'";
							}

							$sql	= "`{$string}` IN(" . implode(',', $args_data) . ")";
						} else {
							$sql	= "`{$string}` = '" . addslashes($args[0]) . "'";
						}
					}
				}

				if($is_finder_mmethod == 1) {
					return $class::find_first($sql);
				} elseif($is_finder_mmethod == 2) {
					return $class::find($sql);
				}
			}

			throw new Exception("Relation method {$name} not found");
		}

		function as_array() {
			$attributes			= [];

			foreach ($this->get_fields() as $key) {
				$attributes[$key]	= $this->$key;
			}

			return $attributes;
		}

		static function truncate() {
			$class	= get_called_class();			
			
			Recordset::query('TRUNCATE TABLE ' . $class::$_table[$class]);
		}

		static function destroy_all($conditions = '1=1') {
			$class	= get_called_class();			
			
			Recordset::query('DELETE FROM ' . $class::$_table[$class] . ' WHERE ' . $conditions);
		}
	}