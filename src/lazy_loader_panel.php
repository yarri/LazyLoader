<?php
class LazyLoaderPanel implements Tracy\IBarPanel {

	protected $title;
	protected $lazy_loader;

	function __construct($lazy_loader, $options = array()){
		$options += array(
			"title" => "LazyLoader",
		);
		$this->title = $options["title"];
		$this->lazy_loader = $lazy_loader;
	}

	function getTab(){
		$p_data = $this->lazy_loader->profilerData();
		$closures_executed = 0;
		foreach($p_data as $k => $v){
			$closures_executed += $v["executed"];
		}

		if(!$closures_executed){
			return $this->title;
		}
		return "<strong>$this->title</strong> ".$closures_executed;
	}

	function getPanel(){
		$p_data = $this->lazy_loader->profilerData();

		$out = array();
		$out[] = '<table>';
		$out[] = '<thead>';
		$out[] = '<tr>';
		$out[] = '<th>Closure</th>';
		$out[] = '<th>Executed</th>';
		$out[] = '</tr>';
		$out[] = '</thead>';
		$out[] = '</body>';
		foreach($p_data as $key => $v){
			$out[] = '<tr>';
			$out[] = '<td>'.$key.'</td>';
			$out[] = '<td>'.$v['executed'].'&times;</td>';
			$out[] = '</tr>';
		}
		$out[] = '</tbody>';
		$out[] = '</table>';
		return join("\n",$out);
	}
}
