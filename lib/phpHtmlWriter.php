<?php

/**
 * Simple PHP HTML Writer
 *
 * @tutorial  http://github.com/ornicar/php-html-writer/blob/master/README.markdown
 * @version   0
 * @author    Thibault Duplessis <thibault.duplessis at gmail dot com>
 * @license   MIT License
 *
 * Website: http://github.com/ornicar/php-html-writer
 * Tickets: http://github.com/ornicar/php-html-writer/issues
 */

require_once(dirname(__FILE__).'/phpHtmlWriterConfigurable.php');
require_once(dirname(__FILE__).'/phpHtmlWriterElement.php');

class phpHtmlWriter extends phpHtmlWriterConfigurable
{
  /**
   * @var phpHtmlWriterCssExpressionParser  the CSS expression parser instance
   */
  protected $cssExpressionParser;
  
  /**
   * @var phpHtmlWriterAttributeArrayParser the attribute array parser instance
   */
  protected $attributeArrayParser;
  
  /**
   * @var array                   the writer options
   */
  protected $options = array(
    'element_class'           => 'phpHtmlWriterElement',
    'encoding'                => 'UTF-8' // used by htmlentities
  );

  /**
   * Instanciate a new HTML Writer
   */
  public function __construct(array $options = array())
  {
    $this->configure($options);
  }

  /**
   * Render a HTML tag
   *
   * Examples:
   * $view->tag('p', 'text content')
   * $view->tag('div#my_id.my_class', 'text content')
   * $view->tag('div', $view->tag('p', 'textual content'))
   * $view->tag('a', array('title' => 'my title'), 'text content')
   *
   * @param   string  $cssExpression      a valid CSS expression like "div.my_class"
   * @param   mixed   $htmlAttributes     additional HTML attributes, or tag content
   * @param   string  $content            tag content if attributes are provided
   * @return  phpHtmlWriterTag            a tag to be rendered
   */
  public function tag($cssExpression, $attributes = array(), $content = null)
  {
    /**
     * use $attributes as $content if needed
     * allow to use 2 or 3 parameters when calling the method:
     * ->tag('div', 'content')
     * ->tag('div', array('id' => 'an_id'), 'content')
     */
    if(empty($content) && !empty($attributes) && !is_array($attributes))
    {
      $content    = $attributes;
      $attributes = array();
    }

    // get the tag and attributes from the CSS expression
    list($tag, $cssAttributes) = $this->getCssExpressionParser()->parse($cssExpression);

    // get the additional HTML attributes passed by the htmlAttributes array
    $attributes = $this->getAttributeArrayParser()->parse($attributes);

    // merge CSS attributes with the attributes array
    $attributes = $this->mergeAttributes($cssAttributes, $attributes);

    /**
     * element object that can be rendered with __toString()
     * @var phpHtmlWriterElement
     */
    $element = new $this->options['element_class']($tag, $attributes, $content);

    return $element;
  }

  /**
   * Get the CSS expression parser instance
   *
   * @return  phpHtmlWriterCssExpressionParser  the CSS expression parser
   */
  public function getCssExpressionParser()
  {
    if(null === $this->cssExpressionParser)
    {
      require_once(dirname(__FILE__).'/phpHtmlWriterCssExpressionParser.php');
      $this->cssExpressionParser = new phpHtmlWriterCssExpressionParser();
    }

    return $this->cssExpressionParser;
  }

  /**
   * Inject another CSS expression parser
   *
   * @param phpHtmlWriterCssExpressionParser $cssExpressionParser a parser instance
   */
  public function setCssExpressionParser(phpHtmlWriterCssExpressionParser $cssExpressionParser)
  {
    $this->cssExpressionParser = $cssExpressionParser;
  }

  /**
   * Get the attribute array parser instance
   *
   * @return  phpHtmlWriterAttributeArrayParser  the attribute array parser
   */
  public function getAttributeArrayParser()
  {
    if(null === $this->attributeArrayParser)
    {
      require_once(dirname(__FILE__).'/phpHtmlWriterAttributeArrayParser.php');
      $this->attributeArrayParser = new phpHtmlWriterAttributeArrayParser(array(
        'encoding' => $this->options['encoding']
      ));
    }

    return $this->attributeArrayParser;
  }

  /**
   * Inject another attribute array parser instance
   *
   * @param phpHtmlWriterCssExpressionParser $cssExpressionParser a, attribute array parser instance
   */
  public function setAttributeArrayParser(phpHtmlWriterAttributeArrayParser $attributeArrayParser)
  {
    $this->attributeArrayParser = $attributeArrayParser;
  }

  protected function mergeAttributes(array $attributes1, array $attributes2)
  {
    // manually merge the class attribute
    if(isset($attributes1['class']) && isset($attributes2['class']))
    {
      $attributes2['class'] = $this->mergeClasses($attributes1['class'], $attributes2['class']);
      unset($attributes1['class']);
    }

    return array_merge($attributes1, $attributes2);
  }

  protected function mergeClasses($classes1, $classes2)
  {
    return implode(' ', array_unique(array_map('trim', array_merge(
      str_word_count($classes1, 1, '0123456789-_'),
      str_word_count($classes2, 1, '0123456789-_')
    ))));
  }

  /**
   * Test method - use tag() instead
   */
  public function renderTag($cssExpression, $attributes = array(), $content = null)
  {
    return $this->tag($cssExpression, $attributes, $content)->render();
  }
}