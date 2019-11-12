<?php 

	class Ccsd_View_Helper_Chart // extends Zend_View_Helper_Abstract 
	{
		/* TODO ****************************************************
		* Gérer les transitions
		* Gérer les imageCharts
		* Gérer l'export CSV
		// *********************************************************/
		
		public $view;
	
		private $count = 0;
		
		private $id;
		
		private $existingTypes = array(
		        'AreaChart', 
		        'BarChart', 
		        'BubbleChart', 
		        'CandlestickChart', 	
		        'ColumnChart', 
		        'ComboChart', 
		        'Gauge', 
		        'GeoChart', 
		        'LineChart',
		        'PieChart', 
		        'ScatterChart', 
				'SteppedAreaChart', 
		        'Table', 
		        'TreeMap'
        );
		
		private $allowedTypes = array(
		        'AreaChart', 
		        'BarChart', 
		        'BubbleChart', 
		        'ColumnChart',
		        'ComboChart', 
		        'Gauge', 
		        'GeoChart',
		        'LineChart',
				'PieChart',
		        'ScatterChart',
		        'SteppedAreaChart',
		        'Table',
		        'TreeMap'
		        
        );
		
		
		public function chart($data, $type = 'bar', $returnJs = false)
		{
			if (! $returnJs) {
                $this->view->jQuery()->addJavascriptFile('https://www.google.com/jsapi');
            }
			
			if (!in_array($type, $this->existingTypes)) {
				throw new Exception("Ce type de graphique n'existe pas : $type");
			}
						
			if (!in_array($type, $this->allowedTypes)) {
				throw new Exception("Ce type de graphique n'est pas supporté : $type");
				// return false;
			}
			
			if (!isset($data['content'])) {
				throw new Exception("Aucune donnée à afficher pour ce graphique : $type");
				// return false;
			}
			
			$this->count++;
			if ($returnJs) {
                return $this->toJs($data, $type);
            }
            return $this->draw($data, $type);
			
		}

        public function toJs($data, $type)
        {
            // Load the Visualization API and the piechart package.

            if ($type == 'Gauge') {
                $package = 'gauge';
            }

            elseif ($type == 'GeoChart') {
                $package = 'geochart';
            }

            elseif ($type == 'Table') {
                $package = 'table';
            }

            elseif ($type == 'TreeMap') {
                $package = 'treemap';
            }

            else {
                $package = 'corechart';
            }

            $script = array('<script type="text/javascript">');

            // Set a callback to run when the Google Visualization API is loaded.
            //$script[] = 'google.load("visualization", "1.0", {"packages":["'.$package.'"]});';

            //$script[] = '$(document).ready(function(){';
            //$script[] = 'google.setOnLoadCallback(draw'.$type.$this->count.');';

            //$script[] = 'function draw'.$type.$this->count.'() {';
            $script[] = 'var data = new google.visualization.arrayToDataTable('.Zend_Json::encode($data['content']).');';

            if (isset($data['options'])) {
                $options = Zend_Json::encode($data['options']);
                $script[] = "var options = $options;";
            }
            else {
                $script[] = "var options = '';";
            }

            // $lines[] = $this->{'new'.$type}();
            if (isset($data['container'])) {
                $container = $data['container'];
            }
            else {
                echo '<div id="chart_'.$this->count.'"></div>';
                $container = 'chart_'.$this->count;
            }

            $script[] = "var chart = new google.visualization.".$type."(document.getElementById('$container'));";
            $script[] = "chart.draw(data, options);";

            //$script[] = '}';
            $script[] = PHP_EOL;
            $script[] = "</script>";

            return implode(PHP_EOL, $script);
        }
		
		
		private function draw($data, $type)
		{
			// Load the Visualization API and the piechart package.
			
			if ($type == 'Gauge') {
				$package = 'gauge';
			}
			
			elseif ($type == 'GeoChart') {
				$package = 'geochart';
			}
			
			elseif ($type == 'Table') {
				$package = 'table';
			}
			
			elseif ($type == 'TreeMap') {
				$package = 'treemap';
			}
			
			else {
				$package = 'corechart';
			}
			
			$script = array();
				
			// Set a callback to run when the Google Visualization API is loaded.
			$this->view->jQuery()->addJavascript('google.load("visualization", "1.0", {"packages":["'.$package.'"]});');
			
			//$script[] = 'google.setOnLoadCallback(draw'.$type.$this->count.');';
				
			//$script[] = 'function draw'.$type.$this->count.'() {';
            $script[] = '$(document).ready(function(){';
            $script[] = 'var data = new google.visualization.arrayToDataTable('.Zend_Json::encode($data['content']).');';
			
			if (isset($data['options'])) {
				$options = Zend_Json::encode($data['options']);
				$script[] = "var options = $options;";
			}
			else {
				$script[] = "var options = '';";
			}
			
			// $lines[] = $this->{'new'.$type}();
			if (isset($data['container'])) {
				$container = $data['container'];
			}
			else {
				echo '<div id="chart_'.$this->count.'"></div>';
				$container = 'chart_'.$this->count;
			}
			
			$script[] = "var chart = new google.visualization.".$type."(document.getElementById('$container'));";
			$script[] = "chart.draw(data, options);";
				
			//$script[] = '}';
			$script[] = '});';
			$script[] = PHP_EOL;
			
			$script = implode(PHP_EOL, $script);
			$this->view->jQuery()->addJavascript($script);

		}
		
		public function setView(Zend_View_Interface $view)
	    {
	        $this->view = $view;
	    }
		
	}

?>