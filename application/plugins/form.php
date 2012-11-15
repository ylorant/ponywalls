<?php
namespace Plugin;
use \DOMDocument;

class Form
{
	private $fields;
	private $errorData;
	private $lastData;
	private $lastError = array();
	
	const VARCHAR = 1;
	const INTEGER = 2;
	const TEXT = 3;
	const ENUM_SELECT = 4;
	const ENUM_RADIO = 5;
	const PASSWORD = 6;
	const BUTTON = 7;
	const SUBMIT = 8;
	const BOOL = 9;
	const EMAIL = 10;
	
	const ALL_FIELDS = 100;
	
	const CHECK_EMAIL = 200;
	const CHECK_URL = 201;
	const CHECK_PASSWORD = 202;
	const CHECK_INTEGER = 203;
	
	const ERR_EMPTY_FIELD = 300;
	const ERR_INVALID_FIELD = 301;
	
	public function __construct()
	{
		$this->fields = array();
		$this->errorData = array();
	}
	
	public function generate($action, $method = 'POST', $files = FALSE)
	{
		$document = new DOMDocument();
		$form = $document->createElement('form');
		$form->setAttribute('method', $method);
		$form->setAttribute('action', $action);
		if($files == TRUE)
			$form->setAttribute('enctype', 'multipart/form-data');
		
		foreach($this->fields as $field)
		{
			$el = '';
			switch($field['type'])
			{
				case Form::TEXT:
					$el = $document->createElement('textarea');
					$el->nodeValue = $field['default'];
					break;
				case Form::ENUM_SELECT:
					$el = $document->createElement('select');
					foreach($field['values'] as $name => $value)
					{
						$option = $document->createElement('option');
						$option->setAttribute('value', $name);
						$option->nodeValue = $value;
						$el->appendChild($option);
					}
					break;
				case Form::ENUM_RADIO:
					if($field['display'])
					{
						$text = $document->createElement('label');
						$text->nodeValue = $field['display'].': ';
						$form->appendChild($text);
					}
					foreach($field['values'] as $name => $value)
					{
						$label = $document->createElement('span');
						$label->nodeValue = $value;
						
						$radio = $document->createElement('input');
						$radio->setAttribute('type', 'radio');
						$radio->setAttribute('value', (is_int($name) ? $value : $name));
						$radio->setAttribute('name', $field['name']);
						
						if($field['default'] == $name)
							$radio->setAttribute('checked', 'checked');
						
						$radio->value = $value;
						$form->appendChild($radio);
						$form->appendChild($label);
					}
					$form->appendChild($document->createElement('br'));
					break;
				case Form::SUBMIT:
					$el = $document->createElement('input');
					$el->setAttribute('type', 'submit');
					$el->setAttribute('value', $field['display']);
					break;
				case Form::VARCHAR:
				case Form::INTEGER:
				case Form::PASSWORD:
				case Form::EMAIL;
				default:
					$el = $document->createElement('input');
					
					if($field['type'] != Form::PASSWORD)
						$el->setAttribute('type', 'text');
					else
						$el->setAttribute('type', 'password');
					
					if($field['default'] !== NULL)
						$el->setAttribute('value', $field['default']);
			}
			
			if($field['type'] != Form::ENUM_RADIO)
			{
				$el->setAttribute('name', $field['name']);
				
				if($field['type'] != Form::SUBMIT && $field['type'] != Form::BOOL)
				{
					if(in_array($field['name'], $this->errorData))
						$el->setAttribute('class', 'invalid');
					
					if(isset($this->lastData[$field['name']]) && $field['type'] != Form::PASSWORD)
						$el->setAttribute('value', $this->lastData[$field['name']]);
					
					foreach($field['attributes'] as $attr => $value)
						$el->setAttribute($attr, $value);
					
					if($field['display'])
					{
						$text = $document->createElement('label');
						$text->nodeValue = $field['display'].': ';
						$form->appendChild($text);
					}
					
					
				}
				
				$form->appendChild($el);
				
				if($field['note'])
				{
					$note = $document->createElement('span');
					$note->nodeValue = 'Note: '.$field['note'];
					$note->setAttribute('class', 'form-note');
					$form->appendChild($note);
				}
				
				$form->appendChild($document->createElement('br'));
			}
		}
		
		$document->appendChild($form);
		
		return $document->saveHTML();
	}
	
	public function addAttribute($name, $attributeName, $value)
	{
		if(!isset($this->fields[$name]))
			return FALSE;
		
		$this->fields[$name]['attributes'][$attributeName] = $value;
	}
	
	public function addNote($field, $text)
	{
		$this->fields[$field]['note'] = $text;
	}
	
	public function addField($name, $displayName, $type = Form::VARCHAR, $allowedValues = NULL, $regexMatch = NULL, $defaultValue = NULL)
	{
		if($regexMatch === NULL)
		{ 
			switch($type)
			{
				case Form::EMAIL:
					$regexMatch = Form::CHECK_EMAIL;
					break;
				case Form::PASSWORD:
					$regexMatch = Form::CHECK_PASSWORD;
					break;
				case Form::INTEGER:
					$regexMatch = Form::CHECK_INTEGER;
					break;
			}
		}
		
		if(!isset($this->fields[$name]))
			$this->fields[$name] = array('display' => $displayName, 'type' => $type, 'name' => $name, 'default' => $defaultValue, 'values' => $allowedValues, 'attributes' => array(), 'regex' => $regexMatch, 'note' => NULL);
		else
			return FALSE;
		
		return TRUE;
	}
	
	public function deleteField($name)
	{
		if(!isset($this->fields[$name]))
			return FALSE;
		
		unset($this->fields[$name]);
		
		return TRUE;
	}
	
	public function getDisplayName($field)
	{
		return $this->fields[$field]['display'];
	}
	
	/* Field check :
	 * 
	 * Check of $check with fields in $data. Here, $key represent the key of a $data element, $value its value :
	 * - field $key does not exists, field $value does not exists : the element is skipped.
	 * - field $key exists, field $value exists : Check similarity between the $key and $value fields
	 * - field $key exists, field $value does not exists : checks the value of $key field, and its accordance with $value. Overrides $regexMatch
	 * 	 set at field declaration.
	 * - field $key does not exists, field $value exists : checks the value of $value field, in accordance with the $regexMatch parameter set at
	 * 	 the field's declaration.
	 */
	public function check($check, $data, $mandatory = Form::ALL_FIELDS)
	{
		$this->errorData = array();
		$this->lastData = $data;
		$this->lastError = array();
		$return = array();
		//Checking emptiness of the fields
		if($mandatory == Form::ALL_FIELDS)
			$mandatory = array_keys($this->fields);
		
		foreach($mandatory as $field)
		{
			if(empty($data[$field]) && $data[$field] !== '0')
			{
				if(!in_array(Form::ERR_EMPTY_FIELD, $return))
					$return[] = $this->lastError[] = Form::ERR_EMPTY_FIELD;
				$this->errorData[] = $field;
			}
		}
		
		//Checking validity of the fields, in accordance with $check array
		foreach($check as $field => $value)
		{
			$error = FALSE;
                        if(in_array($field, $this->errorData))
                                continue;
			if(isset($this->fields[$field]) && isset($data[$field]))
			{
				if(isset($this->fields[$value]) && isset($data[$value]) && $data[$field] != $data[$value])
				{
					$error = Form::ERR_INVALID_FIELD;
					$this->errorData[] = $field;
				}
				elseif((!isset($this->fields[$value]) || !isset($data[$value])) && !$this->_checkValue($field, $data[$field], $value))
				{
					$error = Form::ERR_INVALID_FIELD;
					$this->errorData[] = $value;
				}
			}
			elseif(isset($this->fields[$value]) && isset($data[$value]) && !$this->_checkValue($value, $data[$value]))
			{
				$error = Form::ERR_INVALID_FIELD;
				$this->errorData[] = $value;
			}
			
			if($error && !in_array($error, $return))
				$return[] = $this->lastError[] = $error;
		}
		
		if($return !== array())
			return $return;
		
		return TRUE;
	}
	
	public function sanitize($data)
	{
		foreach($data as $key => &$value)
		{
			if(isset($this->fields[$key]))
			{
				switch($this->fields[$key]['type'])
				{
					case Form::INTEGER:
						$value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);
						break;
					case Form::EMAIL:
						$value = filter_var($value, FILTER_SANITIZE_EMAIL);
						break;
					case Form::BOOL:
						$value = !!$value;
						break;
					default:
						$value = filter_var($value, FILTER_SANITIZE_STRING);
						break;
				}
			}
		}
		
		return $data;
	}
	
	public function lastError()
	{
		$return = array();
		foreach($this->lastError as $error)
		{
			switch($error)
			{
				case Form::ERR_EMPTY_FIELD:
                                        $errors = array();
                                        foreach($this->errorData as $error)
                                                $errors[] = $this->fields[$error]['display']; 
					$return[] = 'Empty field: '.  join(', ',$errors);
					break;
				case Form::ERR_INVALID_FIELD:
					$return[] = 'Invalid field';
					break;
			}
		}
		
		return $return;
	}
	
	private function _checkValue($field, $data, $regex = NULL)
	{
		if($regex != NULL)
			$check = $regex;
		else
			$check = $this->fields[$field]['regex'];
		
		switch($check)
		{
			case Form::CHECK_EMAIL:
				return filter_var($data, FILTER_VALIDATE_EMAIL);
				break;
			case Form::CHECK_PASSWORD:
				return strlen($data) >= 5;
				break;
			case Form::CHECK_URL:
				return filter_var($data, FILTER_VALIDATE_URL);
				break;
			case Form::CHECK_INTEGER:
				return is_numeric($data);
				break;
			default:
				if(!empty($check))
					return preg_match($check, $data);
				else
					return TRUE;
		}
	}
}
