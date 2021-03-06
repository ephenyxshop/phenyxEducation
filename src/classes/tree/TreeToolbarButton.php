<?php


/**
 * Class TreeToolbarButtonCore
 */
abstract class TreeToolbarButtonCore
{
    const DEFAULT_TEMPLATE_DIRECTORY = 'helpers/tree';

    // @codingStandardsIgnoreStart
    protected $_attributes;
    private $_class;
    private $_context;
    private $_id;
    private $_label;
    private $_name;
    protected $_template;
    protected $_template_directory;
    // @codingStandardsIgnoreEnd

    /**
     * TreeToolbarButtonCore constructor.
     *
     * @param      $label
     * @param null $id
     * @param null $name
     * @param null $class
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($label, $id = null, $name = null, $class = null)
    {
        $this->setLabel($label);
        $this->setId($id);
        $this->setName($name);
        $this->setClass($class);
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param $name
     * @param $value
     *
     * @return $this
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setAttribute($name, $value)
    {
        if (!isset($this->_attributes)) {
            $this->_attributes = [];
        }

        $this->_attributes[$name] = $value;

        return $this;
    }

    /**
     * @param $name
     *
     * @return null
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getAttribute($name)
    {
        return $this->hasAttribute($name) ? $this->_attributes[$name] : null;
    }

    /**
     * @param $value
     *
     * @return $this
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setAttributes($value)
    {
        if (!is_array($value) && !$value instanceof Traversable) {
            throw new PhenyxShopException('Data value must be an traversable array');
        }

        $this->_attributes = $value;

        return $this;
    }

    /**
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getAttributes()
    {
        if (!isset($this->_attributes)) {
            $this->_attributes = [];
        }

        return $this->_attributes;
    }

    /**
     * @param $value
     *
     * @return TreeToolbarButtonCore
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setClass($value)
    {
        return $this->setAttribute('class', $value);
    }

    /**
     * @return mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getClass()
    {
        return $this->getAttribute('class');
    }

    /**
     * @param $value
     *
     * @return $this
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setContext($value)
    {
        $this->_context = $value;

        return $this;
    }

    /**
     * @return Context
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getContext()
    {
        if (!isset($this->_context)) {
            $this->_context = Context::getContext();
        }

        return $this->_context;
    }

    /**
     * @param $value
     *
     * @return TreeToolbarButtonCore
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setId($value)
    {
        return $this->setAttribute('id', $value);
    }

    /**
     * @return null
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getId()
    {
        return $this->getAttribute('id');
    }

    /**
     * @param $value
     *
     * @return TreeToolbarButtonCore
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setLabel($value)
    {
        return $this->setAttribute('label', $value);
    }

    /**
     * @return null
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getLabel()
    {
        return $this->getAttribute('label');
    }

    /**
     * @param $value
     *
     * @return TreeToolbarButtonCore
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setName($value)
    {
        return $this->setAttribute('name', $value);
    }

    /**
     * @return null
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getName()
    {
        return $this->getAttribute('name');
    }

    /**
     * @param $value
     *
     * @return $this
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setTemplate($value)
    {
        $this->_template = $value;

        return $this;
    }

    /**
     * @return mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * @param $value
     *
     * @return $this
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setTemplateDirectory($value)
    {
        $this->_template_directory = $this->_normalizeDirectory($value);

        return $this;
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplateDirectory()
    {
        if (!isset($this->_template_directory)) {
            $this->_template_directory = $this->_normalizeDirectory(static::DEFAULT_TEMPLATE_DIRECTORY);
        }

        return $this->_template_directory;
    }

    /**
     * @param $template
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplateFile($template)
    {
        if (preg_match_all('/((?:^|[A-Z])[a-z]+)/', get_class($this->getContext()->controller), $matches) !== false) {
            $controllerName = strtolower($matches[0][1]);
        }

        if ($this->getContext()->controller instanceof ModuleAdminController && file_exists(
                $this->_normalizeDirectory(
                    $this->getContext()->controller->getTemplatePath()
                ).$this->getTemplateDirectory().$template
            )
        ) {
            return $this->_normalizeDirectory($this->getContext()->controller->getTemplatePath())
                .$this->getTemplateDirectory().$template;
        } elseif ($this->getContext()->controller instanceof AdminController && isset($controllerName)
            && file_exists(
                $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(0)).'controllers'
                .DIRECTORY_SEPARATOR.$controllerName.DIRECTORY_SEPARATOR.$this->getTemplateDirectory().$template
            )
        ) {
            return $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(0)).'controllers'
                .DIRECTORY_SEPARATOR.$controllerName.DIRECTORY_SEPARATOR.$this->getTemplateDirectory().$template;
        } elseif (file_exists(
            $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(1))
            .$this->getTemplateDirectory().$template
        )) {
            return $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(1))
                .$this->getTemplateDirectory().$template;
        } elseif (file_exists(
            $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(0))
            .$this->getTemplateDirectory().$template
        )) {
            return $this->_normalizeDirectory($this->getContext()->smarty->getTemplateDir(0))
                .$this->getTemplateDirectory().$template;
        } else {
            return $this->getTemplateDirectory().$template;
        }
    }

    /**
     * @param $name
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function hasAttribute($name)
    {
        return (isset($this->_attributes)
            && array_key_exists($name, $this->_attributes));
    }

    /**
     * @return mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function render()
    {
        return $this->getContext()->smarty->createTemplate(
            $this->getTemplateFile($this->getTemplate()),
            $this->getContext()->smarty
        )->assign($this->getAttributes())->fetch();
    }

    /**
     * @param $directory
     *
     * @return string
     *
     * @deprecated 2.0.0
     */
    protected function _normalizeDirectory($directory)
    {
        $last = $directory[strlen($directory) - 1];

        if (in_array($last, ['/', '\\'])) {
            $directory[strlen($directory) - 1] = DIRECTORY_SEPARATOR;

            return $directory;
        }

        $directory .= DIRECTORY_SEPARATOR;

        return $directory;
    }
}
