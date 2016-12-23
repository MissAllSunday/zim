<?php

namespace Services;
use \JBBCode;

class CustomCodeDefinitionSet extends \JBBCode\DefaultCodeDefinitionSet implements \JBBCode\CodeDefinitionSet
{
	public function __construct()
	{
		parent::__construct();

		$urlValidator = new \JBBCode\validators\UrlValidator();

		// Center tag
		$builder = new \JBBCode\CodeDefinitionBuilder('center', '<p class="text-center">{param}</p>');
		array_push($this->definitions, $builder->build());

		// Code tag
		$builder = new \JBBCode\CodeDefinitionBuilder('code', '<pre>{param}</pre>');
		array_push($this->definitions, $builder->build());

		// Overwrite the img tag.
		$builder = new \JBBCode\CodeDefinitionBuilder('img', '<img class="img-responsive center-block" src="{param}" />');
		$builder->setUseOption(false)->setParseContent(false)->setBodyValidator($urlValidator);

		// Hackish... but meh
		$this->definitions[6] = $builder->build();

		// Moar hackish stuff... yay!
		$builder = new \JBBCode\CodeDefinitionBuilder('img', '<img class="img-responsive center-block" src="{param}" alt="{option}" />');
		$builder->setUseOption(true)->setParseContent(false)->setBodyValidator($urlValidator);
		$this->definitions[7] = $builder->build();
	}
}
