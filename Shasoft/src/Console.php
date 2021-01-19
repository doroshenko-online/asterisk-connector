<?php 
/**
 * Класс консоли для вывода цветного текста
 */
namespace Shasoft\src;
/**
 * Класс консоли для вывода цветного текста
 */
class Console {
	/**
	 * Магический метод для перегрузки статических методов. Перенеправляет вызовы на \Shasoft\src\Output
	 * @param string $name Имя функции
	 * @param array $arguments Аргументы
	 * @see \Shasoft\src\Output
	 */
	static public function __callStatic( string $name , array $arguments ){
		$output = Output::get();
		/*
		if( method_exists($output,$name) ) {
			throw new \Exception('Method [' . $name . '] in class '.get_class($output).' not exists!');
		}
		//*/
		return call_user_func_array([$output,$name],$arguments);
	}
}