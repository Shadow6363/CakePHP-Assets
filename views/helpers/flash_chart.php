<?php
/** Charts in a flash! - FlashChartHelper version 2.0 
 * 
 * The sole purpose of this helper is to integrate OpenFlashChart2 (http://teethgrinder.co.uk/open-flash-chart-2)
 * with cake in an easy to use way. It is based on the work of Joaquin Windmuller and his article on the bakery
 * (found here : http://bakery.cakephp.org/articles/view/open-flash-chart-helper-draw-charts-the-cake-way). It also
 * replaces the behavior made by the same authors as this helper, ChartableBehavior.
 * 
 * Important changes since the Joaquin version :
 * 
 *   * Takes advantages of the new version 2 of OpenFlashChart
 *   * Integrates directly with cake by using data from Model::find('all')
 *   * Unfortunatly the old api no longer works, but hopefully is easier
 *   * Charts and Pies are now interchangeable!
 * 
 * Working chart and graph types :
 * 
 * Line, Line Dot, Line Hollow, 
 * Bar, Bar Filled, Bar Glass, Bar 3D, Bar Sketch, Bar Stack
 * Area Line, Area Hollow, 
 * Pie Chart
 * Scatter, Scatter Line
 * Radar Line, Radar Area (called radar and radar_filled)
 * 
 * Problems and todolist :
 * 
 * @todo Horizontal Bar is not implemented
 * 
 * Install instructions : 
 *
 * Make sure you have these folders and files in the correct locations:
 * 
 * /app/views/helpers/flash_chart.php
 * /app/views/helpers/flash.php
 * /app/vendors/flashchart/open-flash-chart.php
 * /app/vendors/flashchart/*37 other files at the time of this writing*
 * /app/webroot/open-flash-chart.swf
 * /app/webroot/js/swfobject.js
 * /app/webroot/js/json/json2.js
 *
 * Add the helper to yoy controllers "helpers" property:
 * 
 * var $helpers = array('Html','FlashChart'); 
 * 
 * Usage Example 1 : Minimum usage. You will always need at least this.
 * 
 *  <?php
 *		echo $flashChart->begin();
 *		$flashChart->setData(array(1,2,4,8));
 *		echo $flashChart->chart();
 *	?>
 *	<div id="my_chart"></div>
 * 
 * Usage Example 2 : Minimum with 2 datasets :
 *  
 *  <?php
 *		echo $flashChart->begin();
 *		$flashChart->setData(array(1,2,4,8),'{n}',false,'Apples');
 *		$flashChart->setData(array(3,4,6,9),'{n}',false,'Oranges');
 *		echo $flashChart->chart('line',array('colour'=>'green'),'Apples');
 *		echo $flashChart->chart('line',array('colour'=>'orange'),'Oranges');
 *	?>
 *	<div id="my_chart"></div>
 * 
 * 
 * Usage Example 2 : 
 * 
 * <?php 
 *		foreach ($data as $key => $model) {
 *			$data[$key]['Day']['date'] = $time->format('d.M',$data[$key]['Day']['date']);
 *		}
 *	 
 *		echo $flashChart->begin('SteppChart', 500,500);		
 *		
 *		$flashChart->setTitle('Steppometer','{color:#880a88;font-size:35px;padding-bottom:20px;}');
 *	
 *		$flashChart->setData($data);
 *		$flashChart->setNumbersPath( '{n}.Day.count' );
 *		$flashChart->setLabelsPath( 'Default.{n}.Day.date' );
 *		
 *		$flashChart->setLegend('x','Dato');
 *		$flashChart->setLegend('y','Skritt', '{color:#AA0aFF;font-size:40px;}' );
 *		
 *		$flashChart->axis('x',array('tick_height' => 10,'3d' => -10));
 *		$flashChart->axis('y',array('range' => array(0,10000,1000)));
 *					
 *		echo $flashChart->pie();
 *		
 *	?>
 *	<div id="SteppChart"></div>
 * 
 * Usage Example 3 :
 *  
 *  <?php
 * 	$grades = array('A'=>6,'B'=>5,'C'=>4,'D'=>3,'E'=>2,'F'=>1);
 *	foreach ($data as $key => $model) {		
 *		$data[$key]['Event']['value'] = $grades[ $model['Event']['grade'] ];
 *	}
 *	
 *	$labels = array();
 *	foreach ($data as $key => $label) {
 *		$labels[$key] = $time->nice($label['Event']['when'] );
 *	}
 *	
 *	echo $flashChart->begin(); 
 *	
 *	$flashChart->setTitle('"Events" by Grade','{font-size:50px;color:#AA66AA;}'); 
 *	
 *	$flashChart->setLegend('x','Dato');
 *	$flashChart->setLegend('y','Grade', '{color:#AA0aFF;font-size:40px;}' );
 *	
 *	$flashChart->axis('x',array('labels' => $labels,'tick_height' => 20));
 *	$flashChart->axis('y',array('range' => array(0, 6, 1),'labels' => array('','F','E','D','C','B','A')));
 *		
 *	$flashChart->setData($data,'/Event/value'); 
 *	echo $flashChart->chart('bar_3d', array('colour'=>'#aa55AA'));
 *  ?>
 *  <div id="my_chart"></div>
 * 
 * Usage Example 4 : Scatter
 * 
 *  <?php
 *		$data = array();	
 *		for( $i=0; $i<360; $i+=5 )
 *		{
 *		    $data[] = array(
 *		    	'x' => number_format(sin(deg2rad($i)), 2, '.', ''),
 *		    	'y' => number_format(cos(deg2rad($i)), 2, '.', '') 
 *		    );    
 *		}
 *		echo $flashChart->begin('progress',500,400);
 *		$flashChart->setData($data);
 *		$flashChart->setTitle('Scatter');
 *		$flashChart->axis('x',array('range' => array(-2,3,1)));
 *		$flashChart->axis('y',array('range' => array(-2,2,1)));	
 *		$flashChart->ready = 'alert("ready");' ;
 *		$flashChart->loading = 'alert("loading");' ;
 *		echo $flashChart->chart('scatter');	
 *	?><div id="progress"></div>
 * 
 * Usage Example 5 : Radar
 * 
 * <?php
 *	echo $flashChart->begin('progress', 600, 600);
 *	$flashChart->setTitle('Radar');
 *	$flashChart->setData(array(3, 4, 5, 4, 3, 3, 2.5));
 *	$flashChart->setRadarAxis(array(
 *			'max' => 5, 
 *			'steps' => 1, 
 *			'colour' => '#EFD1EF', 
 *			'grid_colour' => '#EFD1EF', 
 *			'label_colour' => '#343434', 
 *			'labels' => array('0', '1', '2', '3', '4', '5')));
 *	
 *	$flashChart->setToolTip(null, array('proximity' => true));
 *	echo $flashChart->chart('radar', array(
 *			'type' => 'area_hollow', 
 *			'halo_size' => 1, 
 *			'width' => 1, 
 *			'dot_size' => 4, 
 *			'colour' => '#45909F', 
 *			'fill_colour' => '#45909F', 
 *			'fill_alpha' => 0.4, 
 *			'loop' => true)	
 *	);
 *	?><div id="progress"></div>
 * 
 * Usage Example 6 : Stacked Bars
 * 
 * <?php
 *		echo $flashChart->begin('progress', 1100, 700);
 *		$flashChart->setTitle('Stacked Bars');		
 *		$flashChart->axis('y',array('range' => array(0, 14, 7)));	
 *	 	$flashChart->setStackColours(array('#0000ff','#ff0000','#00FF00')); 
 *		$flashChart->setData(array(
 *			array(65,15,20),
 *			array(45,15,40),
 *			array(51,29,20),
 *			array(15,35,50),
 *		));
 *		echo $flashChart->chart('bar_stack');	
 *	?><div id="progress"></div>
 * 
 *** NB Changes from version 2 *****
 * 
 * - Introduction of datasetName for when using multiple datasets
 * - Renamed render() to chart() and render*Type*() to *type*()
 * - Labels path is stored once
 * - legend() changed name to setLegend()
 * - Begin now returns the common javascript and embeds the libraries  
 *
 * To update, make these changes to your views : 
 * 
 * - add echo to all ur $flashChart->begin();
 * - rename all legend() calls to setLegend();
 * - rename all render() to chart();
 * - rename renderType() to type(), ie : renderPie() to pie();
 * - When using multiple datasets, name them, see example 2 above.
 * 
 * @author Alexander Morland aka 'alkemann' @ Maritime Colours AS
 * @author Eskil Mjelvaa Saadtvedt
 * @author Ronny Vindenes
 * @author Carl Erik Fyllingen
 * @modified 4 des. 2008 by Alexander
 * @category Cake Helper
 * @license MIT
 * @version 3.1.1
 *  
 **/
App::import('Vendor', 'flashchart/open-flash-chart');
class FlashChartHelper extends AppHelper {	
	public $helpers = array('Flash','Javascript');
	
	/**
	 * The Vendor OpenFlashChart object. The helper
	 * is basically just a cake front end for this 
	 * object.
	 *
	 * @access public
	 * @var object
	 */
	public $Chart = null;
	
	/**
	 * Name and location of swf relative to /app/webroot/
	 *
	 * @var string
	 */
	public $swf = 'flash/open-flash-chart.swf';
	
	/**
	 * The number data to be used to generate the charts. The dataset can be in any
	 * shape, since the usage of Set::extract within the helper will make sure
	 * the correct values are found. It is suggested to use the result of a
	 * Model::find('all'); 
	 * 
	 * The first chart, is stored in $data[0] and the 2nd in $data[1] etc.
	 *
	 * @var array
	 */
	public $data = array();
	
	/**
	 * Add JAVASCRIPT CODE to this variable to define the ofc_ready function
	 * that is an auto callback in the OFC vendor. Function is called
	 * when the flash is ready.
	 *
	 * @example $this->Chart->ready = 'alert('ready');';
	 * @var string
	 */
	public $ready = '';
	
	/**
	 * Add JAVASCRIPT CODE to this variable to define the open_flash_chart_data
	 * function that is auto callback in the OFC vendor. Function is called
	 * when the data is beeing loaded.
	 *
	 * @example 'alert('loading');'
	 * @var string
	 */
	public $loading = '';
	
	/**  PRIVATE variables **/
	
	private $stackColours = array();
	
	/** /Private vars **/
	
	/*** DEFAULT VALUES ***/
	
	// Default background color
	private $bg_colour = '#FFFFFF';
	// Default grid color
	private $grid_colour = '#CCCCCC';
	// Default title style
	private $title_style = '{color:#EE0000;font-size:40px;text-align:left;padding:0 0 15px 30px;}';
	// Default legend style
	private $legend_style = '{font-size: 20px; color: #778877}';
	// Tooltip template is stored here at runtime
	private $tooltip = null;
	// Labels path (As per Set::extract) is stored here at runtime
	private $labelsPath = false;
	// Numbers path (Set::extract) is stored here at runtime
	private $numbersPath = NULL;
	// Default settings, also default parameters for the FlashChart::begin() method
	private $settings = array('div' => 'my_chart', 'width' => 800, 'height' => 350);
	// Default axis ranges
	private $defaultRange = array('x' => array(0, 10, 1), 'y' => array(0, 10, 1));
	// Default scatter chart options
	private $scatter_options = array(
			'x_key' => 'x', 
			'y_key' => 'y', 
			'colour' => '#AACC99', 
			'size' => 3);
	// container for Spoke Labels, null = not used
	private $spoke_labels = null;
	// Default Radar axis
	private $radarAxis = array(
		'max' => 5,
		'steps' => 1,
		'colour' => '#AA2222',
		'grid_colour' => '#CCCCCC',
		'label_colour' => '#777777'
	);
	/** /DEFAULT VALUES **/
	
	/**
	 * Initialize the helper, select the dom obejct for the chart and set the width and height.
	 *
	 * @param string $div HTML attribute ID of the target div
	 * @param int $width pixel width of the flash
	 * @param int $height pixel height of the div
	 * @return string Javascript library includes
	 */
	public function begin($div = 'my_chart', $width = null, $height = null) {
		if (is_numeric($width)) {
			$this->settings['width'] = $width;
		}
		if (is_numeric($height)) {
			$this->settings['height'] = $height;
		}
		if (is_string($div)) {
			$this->settings['div'] = $div;
		}
		$this->Chart = new open_flash_chart();
		return $this->scripts();
	}
	
	/**
	 * Add a dataset to be rendered by the helper.  
	 * Always call this method at least once, and you must call it after begin() and 
	 * before axis(), or else you may get errors. This tells the helper what data 
	 * to generate graphs from. You can call it multiple times to put in multiple
	 * datasets. You must call the render method in the same order you set the data.
	 * You can optionally set the extract paths (see cake documentation for 
	 * Set::extract() ) directly with this method or use the specific methods (
	 * setNumbersPath() and setLabelsPath() ).
	 * 
	 * The data can be in any format you want, using the paths to tell the helper
	 * how to find your data. If you give no path, neither here, nor with the above
	 * mentioned methods, it expects the data array to be array(12,32,15,23).
	 *    
	 * @example $flashChart->setData(array(1,5,23,35));
	 * @example $flashChart->setData($users,'/User/age','/User/name');
	 * @example $flashChart->setData($data,'{n}.Event.grade','{n}.Girl.name', 'Girls');
	 * @param array $data
	 * @param string $numbersPath
	 * @param string $labelsPath (if string, this dataset will overwrite any previous label path.)
	 * @param string $datasetName The name to be used to associate charts with data
	 */
	public function setData($data, $numbersPath = '{n}', $labelsPath = false, $datasetName = 'default') {
		$this->data[$datasetName] = $data;
		if (is_string($numbersPath)) {
			$this->numbersPath[$datasetName] = $numbersPath;
		}
		if (is_string($labelsPath)) {
			if (substr($labelsPath,0,1) == '/') {
				$labelsPath = '/'.$datasetName.$labelsPath;
			} else {
				$labelsPath = $datasetName.'.'.$labelsPath;				
			}
			$this->labelsPath = $labelsPath;
		}
	}

	/**
	 * Renders the javascript with data and customization for one graph chart. To be called last, but 
	 * may be called multiple times with different datasetNames for different datasets or different
	 * type (and options) for different display of the same data in the same chart.
	 * What options are valid vary from chart type to chart type, and the helper is set up in such 
	 * a way as to pass the options on to the vendor, therefore letting you use an updated vendor 
	 * without changes to the helper. This also means that the helper doesnt know (or care) what 
	 * options you send, but if they do not exist in the vendor, you will throw an error.
	 * 
	 *   For options documentation see;
	 *   http://teethgrinder.co.uk/open-flash-chart-2/
	 *  
	 * @example echo $flashChart->chart();
	 * @example echo $flashChart->chart('bar_3d', array('colour'=>'#aa55AA'));
	 * @example echo $flashChart->chart('line',array('colour'=>'green'),'Apples');
	 * @param string $type Valid types - see doc in top
	 * @param array $options varies depending on type. See vendor documentation
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function chart($type = 'bar', $options = array(), $datasetName = 'default') {	
		switch ($type){
			case 'pie':
				return $this->pie($options, $datasetName);
			break;
			case 'sketch':
				return $this->sketch($options, $datasetName);
			break;
			case 'scatter':
				return $this->scatter($options, $datasetName);
			break;
			case 'scatter_line':
				$options['type'] = $type;
				return $this->scatter($options, $datasetName);
			break;
			case 'bar_stack' :				
				return $this->barStack($options, $datasetName);
			break;
			case 'radar':
				return $this->radar($options, $datasetName);
			break;
			case 'radar_filled':
				$options['type'] = 'filled';
				return $this->radar($options, $datasetName);
			break;
			case 'line': case 'line_dot': case 'line_hollow':
			case 'bar': case 'bar_filled': case 'bar_glass': case 'bar_3d':
			case 'area_line': case 'area_hollow':
			
				if (empty($this->data[$datasetName])) {
					return false;
				}	
				$this->Chart->set_bg_colour($this->bg_colour);
				$element = new $type();
				foreach ($options as $key => $setting) {
					switch ($key) {
						case 'line_style':
							$line_style = new line_style($setting[0],$setting[1]);
		            		$element->line_style($line_style);
						break;
						default:
			                $set_method = 'set_' . $key;
			                if (is_array($setting)) {
			                    $element->$set_method($setting[0], $setting[1]);
			                } else {
			                    $element->$set_method($setting);
			                }
			            break;
					}		          
				}
				if (!empty($this->tooltip) ) {
					$element->set_tooltip($this->tooltip);
				}
				$numbers = $this->getNumbers($datasetName);
				$element->set_values($numbers);
				$this->Chart->add_element($element);
				
				return $this->renderData();
				
			break;
			default:
				return false;
		}
	}
	
	/**
	 * Alias for FlashChart::chart('bar_stack');
	 *
	 * @param array $options
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function barStack($options = array(), $datasetName = 'default') {
		if (empty($this->data[$datasetName])) {
			return false;
		}	
		$bar_stack = new bar_stack();
		foreach ($this->data[$datasetName] as $values) {
			$tmp = array();
			if (sizeof($this->stackColours) == sizeof($values)) {
				foreach ($values as $key => $value) {
					$tmp[] = new bar_stack_value($value, $this->stackColours[$key]);
				}
			} else {
				$tmp = $values;
			}
			$bar_stack->append_stack($tmp);
		}
		$this->Chart->set_bg_colour($this->bg_colour);
		$this->Chart->add_element($bar_stack);
		return $this->renderData();
	}
	
	/**
	 * Alias for FlashChart::chart('scatter'), this method renders only
	 * the scatter chart type
	 *
	 * Online documentation :
	 * http://teethgrinder.co.uk/open-flash-chart-2/scatter-chart.php
	 * 
	 * @param array $options
	 * 		valid option keys : colour, size, x_key, y_key
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function scatter($options = array(), $datasetName = 'default') {
		if (empty($this->data[$datasetName])) {
			return false;
		}	
		$options = am($this->scatter_options, $options);
		if (isset($options['type']) && $options['type'] == 'scatter_line') {
			$scatter = new scatter_line($options['colour'], $options['size']);
		} else {
			$scatter = new scatter($options['colour'], $options['size']);
		}		
		$values = array();
		foreach ($this->data[$datasetName] as $row) {
			$values[] = new scatter_value($row[$options['x_key']], $row[$options['y_key']]);
		}
		$scatter->set_values($values);
		$this->Chart->add_element($scatter);
		$this->Chart->set_bg_colour($this->bg_colour);
		return $this->renderData();
	
	}
	
	/**
	 * This is an alias for FlashChart::chart('bar_scetch',$options);
	 *
	 * Unfortunatly the Sketch class takes in is options as constructor
	 * values instead of using the set methods like the other classes. 
	 * 
	 * @param array $options
	 * 		valid option keys : colour, outline_colour, fun_factor
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function sketch($options = array(), $datasetName = 'default') {
		if (empty($this->data[$datasetName])) {
			return false;
		}
		$this->Chart->set_bg_colour($this->bg_colour);
		$element = new bar_sketch($options['colour'], $options['outline_colour'], $options['fun_factor']);
		if (!empty($this->tooltip)) {
			$element->set_tooltip($this->tooltip);
		}
		$numbers = $this->getNumbers($datasetName);
		$element->set_values($numbers);
		$this->Chart->add_element($element);
		return $this->renderData();
	}	
	
	/**
	 * Alias for FlashChart::chart('radar'); 
	 *
	 * The Radar chart needs special axis and also
	 * have special methods for stokes and labes
	 * 
	 * @todo Each value can have it's own tooltip using the dot_value class
	 *
	 * @example echo $flashChart->radar(array('loop'=>false','colour'=>'336699'));
	 * @example echo $flashChart->radar(array('type'=>filled'),'Dataset2');
	 * @param array $options
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function radar($options = array(), $datasetName = 'default') {
		if (empty($this->data[$datasetName])) {
			return false;
		}	
		$this->Chart->set_bg_colour($this->bg_colour);
		
		
		if (isset($options['type']) && $options['type'] == 'filled') {
			$line = new area_hollow();
			
		} else {
			$line = new line_hollow();
			if (!isset($options['loop']) || (isset($options['loop']) && $options['loop'])) {
				$line->loop();
			}
			if (isset($options['loop'])) {
				unset($options['loop']);
			}
			
		
		}
		
		$values = $this->getNumbers($datasetName);
		/* @todo code below is not getting expected result
		if (isset($options['tooltip_path'])) {
			$numbers = $values;
			$values = array();
			$tooltips = Set::extract($xpath,$this->data[$datasetName]);
			if (isset($options['tooltip_colour'])) {
				$colour = $options['tooltip_colour'];
				unset($options['tooltip_colour']);	
			} else {
				$colour = $this->grid_colour;
			}
			foreach ($numbers as $key => $number) {
				$tmp = new dot_value( $number, $colour );
		    	$tmp->set_tooltip($tooltips[$key]);
		    	$values[] = $tmp;
			}			
			unset($options['tooltip_path']);	
		}*/
		
		if (isset($options['type'])) {
			unset($options['type']);	
		}
		foreach ($options as $key => $setting) {
			$set_method = 'set_' . $key;
			if (is_array($setting)) {
				$line->$set_method($setting[0], $setting[1]);
			} else {
				$line->$set_method($setting);
			}			
		}		
		$radar_axis_object = new radar_axis( $this->radarAxis['max'] );
		$radar_axis_object->set_steps($this->radarAxis['steps']);
		$radar_axis_object->set_colour( $this->radarAxis['colour'] );
		$radar_axis_object->set_grid_colour( $this->radarAxis['grid_colour'] );		
		if (!empty($this->radarAxis['labels']) ) {
			$labels = new radar_axis_labels( $this->radarAxis['labels'] );
			$labels->set_colour( $this->radarAxis['label_colour'] );
			$radar_axis_object->set_labels( $labels );
		}		
		if (!is_null($this->spoke_labels)) {
			$radar_axis_object->set_spoke_labels( $this->spoke_labels );
		}
		$this->Chart->set_radar_axis( $radar_axis_object );	 		
		
		$line->set_values($values);		
		$this->Chart->add_element($line);
		return $this->renderData();
	}
	
	/**
	 * This is an alias to FlashChart::chart('pie') that is only used for the 
	 * pie type.
	 *
	 * For options documentation; 
	 * http://teethgrinder.co.uk/open-flash-chart-2/pie-chart.php
	 * 
	 * @example echo $flashChart->renderPie();
	 * @example echo $flashChart->renderPie(array('animate'=>false);
	 * @param array $options
	 * 		Valid options : values, animate, start_angle, tooltip
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return string
	 */
	public function pie($options = array(), $datasetName = 'default') {
		if (empty($this->data[$datasetName])) {
			return false;
		}
		$this->Chart->set_bg_colour($this->bg_colour);
		$pie = new Pie();
		foreach ($options as $key => $setting) {
			$set_method = 'set_' . $key;
			$pie->$set_method($setting);
		}
		$pies = array();
		$labels = Set::extract($this->data, $this->labelsPath);
		$numbers = $this->getNumbers($datasetName);
		foreach ($numbers as $key => $value) {
			if (isset($labels[$key]) && is_string($labels[$key])) {
				$pies[] = new pie_value($value, $labels[$key]);
			} else {
				$pies[] = $value;
			}
		}
		$pie->set_values($pies);
		$this->Chart->add_element($pie);
		return $this->renderData();
	}
	
	/**
	 * Sets the tool tip for the chart by using a string with replaceable
	 * codewords like #val#. Check OFC2 for documentation. Also you can style
	 * the tooltips look and behavior using the options parameter.
	 * 
	 * Documentation:
	 * http://teethgrinder.co.uk/open-flash-chart-2/tooltip-menu.php
	 * 
	 * @example $flashChart->setToolTip('#val#%');
	 * @param string $tooltip
	 * @param array $options see OFC2 doc for valid options
	 */
	public function setToolTip($tooltip = '', $options = array()) {
		if (is_string($tooltip))
			$this->tooltip = $tooltip;
		if (!empty($options)) {
			$tool_tip_object = new tooltip();
			foreach ($options as $key => $setting) {
				$set_method = 'set_' . $key;
				$tool_tip_object->$set_method($setting);
			}
			$this->Chart->set_tooltip($tool_tip_object);
		}
	}
	
	/**
	 * Sets the title above the chart. You can also style it with
	 * css as the second parameter.
	 *
	 * @example $flashChart->setTitle('Awesomeness');
	 * @example $flashChart->setTitle('Coolness, by date','{font-size:26px;}');
	 * @param string $title_text
	 * @param string $style css
	 */
	public function setTitle($title_text, $style = '') {
		$title = new title($title_text);
		if (empty($style)) {
			$style = $this->title_style;
		}
		$title->set_style($style);
		$this->Chart->set_title($title);
	}
	
	/**
	 * Set the descriptive texts next to the axies to describe their meaning.
	 * You can also style it directly here using CSS.
	 *
	 * @example $flashChart->setLegend('x','Time of day');
	 * @example $flashChart->setLegend('y','Coolness factor','{font-size:10px;color:#FF0000;}');
	 * @param string $axis 'x' or 'y'
	 * @param string $title
	 * @param string $style css
	 */
	public function setLegend($axis, $title, $style = '') {
		$legend_object_name = $axis . '_legend';
		$legend_set_method = 'set_' . $axis . '_legend';
		$legend_object = new $legend_object_name($title);
		if (empty($style)) {
			$style = $this->legend_style;
		}
		$legend_object->set_style($style);
		$this->Chart->$legend_set_method($legend_object);
	}
	
	/** 
	 * Use this method to set up the axis' range and labels. There are also a number
	 * of options (mostly styling) that can be set up. The two axis have different 
	 * options, but a full documentation can be found on the links given under.
	 * Importantly though, the y has a range option that takes an array with 3 values
	 * (minimum value, max value and step size). On the x axis you will often want
	 * to use the labels from the dataset and the helper will add those labels if
	 * you have defined a proper labels path, either as the third parameter of 
	 * setDate() or using the setLabelsPat() method. Note, that even if you require
	 * no options for the x-axis, you will have to call this method on that axis
	 * for it to use those labels.
	 *
	 * See documentation for options ;
	 * http://teethgrinder.co.uk/open-flash-chart-2/x-axis.php
	 * http://teethgrinder.co.uk/open-flash-chart-2/y-axis.php
	 * 
	 * @example $flashChart->axis('x'); //Sets labels from dataset
	 * @example $flashChart->axis('y', array('range'=>array(0,50,5), 'tick_length'=>15);
	 * @param string $axis 'x' or 'y'
	 * @param array $options
	 */
	public function axis($axis, $options = array()) {
		$axis_object_name = $axis . '_axis';
		$axis_set_method = 'set_' . $axis . '_axis';
		$axis_object = new $axis_object_name();
		
		foreach ($options as $key => $setting) {
			// special options set direcly bellow
			if (in_array($key, array('labels', 'range')))
				continue;
			$set_method = 'set_' . $key;
			if (is_array($setting)) {
				switch ($key){
					case 'colours':
						$axis_object->set_colours($setting[0], $setting[1]);
					break;
					default:
						$axis_object->$set_method($setting);
				}
			} else {
				$axis_object->$set_method($setting);
			}
		}
		// that wich must always be set :
		if (!isset($options['colour'])) {
			$axis_object->set_colour($this->grid_colour);
		}
		if (!isset($options['grid_colour'])) {
			$axis_object->set_grid_colour($this->grid_colour);
		}
		
		if (isset($options['range'])) {
			if (isset($options['range'][0])) {
				$min = $options['range'][0]; 
			} else {
				$min = $this->defaultRange[$axis][0];
			}
			if (isset($options['range'][1])) {
				$max = $options['range'][1]; 
			} else {
				$max = $this->defaultRange[$axis][1];
			}
			if (isset($options['range'][2])) {
				$step = $options['range'][2]; 
			} else {
				$step = $this->defaultRange[$axis][2];
			}
			
			if ($axis == 'y') {
				$axis_object->set_range($min, $max, $step);
			} else { // $axis == 'x'
				$axis_object->set_range($min, $max);
				$axis_object->set_steps($step);
			}			
		} else {
			if ($axis == 'y') {
				$axis_object->set_range($this->defaultRange[$axis][0], $this->defaultRange[$axis][1], $this->defaultRange[$axis][2]);
			}
		} 
		if ($axis == 'x' && is_string($this->labelsPath) && !empty($this->labelsPath) ) {
			$labels = Set::extract($this->data, $this->labelsPath);
			$axis_object->set_labels_from_array($labels);
		} elseif (isset($options['labels']) && is_array($options['labels']) && $axis == 'x') {
			$axis_object->set_labels_from_array($options['labels']);
		} elseif (isset($options['labels'])) {
			$axis_object->set_labels($options['labels']);
		}		
		$this->Chart->$axis_set_method($axis_object);
	}
	
	/**
	 * When using multiple charts in one diagram, it may be useful to have a second
	 * y-axis for different values. At the moment this feature is not perfectly 
	 * implemented in the vendor, among other problems, all charts will use the left
	 * y-axis' range for displaying their values.
	 * 
	 * The options it takes in is documented here;
	 * http://teethgrinder.co.uk/open-flash-chart-2/y-axis-right.php
	 *
	 * @param array $options
	 */
	public function rightAxis($options = array()) {
		$y = new y_axis_right();
		if (!empty($options)) {
			foreach ($options as $key => $setting) {
				$set = 'set_' . $key;
				if (is_array($setting) && sizeof($setting) == 2) {
					$y->$set($setting[0], $setting[1]);
				} else {
					$y->$set($setting);
				}
			
			}
		}
		$this->Chart->set_y_axis_right($y);
	}
			
	/**
	 * Radar charts are circular and this method sets the grid options, more
	 * than an axis really. The options it takes in define the "height" and
	 * steps of the grid and its colour. You can also set the labels for the 
	 * y axis (or what you can think of as the radius).
	 *
	 * @param array $options
	 * 		valid option keys : max, steps, colour, grid_colour, label_colour
	 * @param array $labels
	 * @return string
	 */
	public function setRadarAxis($options = array(), $labels = array()) {
		$this->radarAxis  = am($this->radarAxis, $options);
		if (!empty($labels)) {
			$this->radarAxis['labels'] = $labels;
		}
		return  $this->radarAxis;
	}

	/**
	 * Spokes are the labels that name the "radius"-axis of the chart
	 *
	 * @example $flashChart->setRadarSpokes(array('weight','height','strength'));
	 * @example $flashChart->setRadarSpokes(array('red','green','blue'),'#AA3377');
	 * @param array $spokes
	 * @param string $colour
	 */
	public function setRadarSpokes($spokes, $colour = null) {		
		if (!$colour) {
			$colour = $this->defaultSpokeColour;
		}		
		$this->spoke_labels = new radar_spoke_labels( $spokes  );		
		$this->spoke_labels->set_colour( $colour );		
	}
	
	/**
	 * Tells the helper where to find the numbers to generate the graph with. 
	 * This is the same functionality as the 2nd paramter of the setData() 
	 * method. You do not need to set it both places.
	 *
	 * @param string $path
	 * @param string $datasetName The name to be used to associate charts with data
	 */
	public function setNumbersPath($path, $datasetName = 'default') {
		$this->numbersPath[$datasetName] = $path;
	}
	
	/**
	 * Tells the helper where to find the labels for the X axis. 
	 * This is the same functionality as the third paramter of the setData() 
	 * method. You do not need to set it both places.
	 * NB. The path should start with the name of the dataset
	 *
	 * @example $flashChart->setLabelsPath('Default.{n}.User.name');
	 * @param string $path
	 */
	public function setLabelsPath($path) {
		$this->labelsPath = $path;
	}
	
	/**
	 * Set the background color for the entire diagram. Optional. Will
	 * use the default stored in FlashChart::bg_colour if not used.
	 *
	 * @param string $colour #AA0000
	 */
	public function setBgColour($colour) {
		$this->bg_colour = $colour;
	}
	
	/**
	 * For the chart type Bar_stack this method sets the colours of the bars.
	 *
	 * @param array $colours
	 */
	public function setStackColours($colours = array()) {
		$this->stackColours = $colours; 
	}
	
	/**
	 * Private method used by the helper to extract the data from the array based on
	 * the numbersPath and cast them to Integer if they are string (as they often are
	 * coming from a cake model.
	 *
	 * @access private
	 * @param string $datasetName The name to be used to associate charts with data
	 * @return array
	 */
	private function getNumbers($datasetName = 'default') {
		if ($this->numbersPath[$datasetName] != '{n}') {
			$numbers = Set::extract($this->data[$datasetName], $this->numbersPath[$datasetName]);
		} else {
			$numbers = $this->data[$datasetName];
		}
		/** the +0 is doing a math function to force php to autocast the value to a numeric type */
		foreach ($numbers as $key => $value) {
			if (is_numeric($value)) {
				$numbers[$key] = $value + 0;
			}			
		}
		return $numbers;
	}

	/**
	 * returns the data array in a json array way.
	 *
	 * @access private
	 * @return string
	 */
	private function renderData() {			
		return $this->Javascript->codeBlock(
			'function open_flash_chart_data() {
				'.$this->loading.'
			    return JSON.stringify(data);
			}
			var data = ' . $this->Javascript->object($this->Chart) . ';'
		);
	}

	/**
	 * Private method that writes the needed javascripts and embeds the flash. As of now,
	 * the helper does NOT degrade for browsers that do not allow js.
	 *
	 * @access private
	 * @return string
	 */
	private function scripts() {
		$ret = $this->Flash->renderSwf($this->swf,$this->settings['width'],$this->settings['height'],$this->settings['div']);
		$ret .= $this->Javascript->link('json/json2');		
		$ret .= $this->Javascript->codeBlock(
		'	
			function ofc_ready()
			{
			  '.$this->ready.'
			}
			
			function findSWF(movieName) {
			  if (navigator.appName.indexOf("Microsoft")!= -1) {
			    return window[movieName];
			  } else {
			    return document[movieName];
			  }
			}			    
		');
		return $ret;
  	}
}

?>