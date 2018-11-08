<?php
namespace Jaspersoft\Dto\Report;

/**
 * Class InputControl
 * @package Jaspersoft\Dto\Report
 */
class InputControl
{
    /**
     * @var string
     */
    public $uri;
    /**
     * @var string
     */
    public $id;
    /**
     * @var string
     */
    public $error;

    public $label;

    public $type;

    public $mandatory;

    #public $dataType = array();
    public $dataType;

	public function __construct($uri = null, $id = null, $label = null, $type = null, $mandatory = null, $dataType = null, $error = null)
    {
		$this->uri = (!empty($uri)) ? strval($uri) : null;
		$this->id = (!empty($id)) ? strval($id) : null;
		$this->label = (!empty($label)) ? strval($label) : null;
		$this->type = (!empty($type)) ? strval($type) : null;
		$this->mandatory = (!empty($mandatory)) ? strval($mandatory) : null;
		$this->dataType = (!empty($dataType)) ? strval($dataType) : null;
		$this->error = (!empty($error)) ? strval($error) : null;
	}

	public static function createFromJSON($json)
    {
		$data_array = json_decode($json, true);
		$result = array();
		#print_r($data_array);
		if(is_array($data_array)) {
		foreach($data_array['inputControl'] as $k) {
			$temp = @new self($k['uri'], $k['id'], $k['label'], $k['type'], $k['mandatory'], $k['dataType']['type'], $k['error']);
			#print_r($k['dataType']);
			#if (!empty($k['dataType'])) {
			#	foreach ($k['dataType'] as $o) {
			#		@$temp->addDataType($o['type']);
			#	}
			#}
			$result[] = $temp;
		}
		}
		return $result;
	}

	#private function addDataType($type)
    #{
		#$temp = array('type' => strval($type));
		#$this->dataType[] = $temp;
	#}

}
