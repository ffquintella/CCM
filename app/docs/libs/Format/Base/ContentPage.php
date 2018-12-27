<?php namespace Todaymade\Daux\Format\Base;

use Todaymade\Daux\Config;
use Todaymade\Daux\ContentTypes\ContentType;
use Todaymade\Daux\Tree\Content;

abstract class ContentPage extends SimplePage
{
    /**
     * @var Content
     */
    protected $file;

    /**
     * @var Config
     */
    protected $params;

    /**
     * @var ContentType
     */
    protected $contentType;

    protected $generatedContent;

    public function __construct($title, $content)
    {
        $this->initializePage($title, $content);
    }

    public static function fromFile(Content $file, $params, ContentType $contentType)
    {
        $page = new static($file->getTitle(), $file->getContent());
        $page->setFile($file);
        $page->setParams($params);
        $page->setContentType($contentType);

        return $page;
    }

    public function setParams(Config $params)
    {
        $this->params = $params;
    }

    /**
     * @param ContentType $contentType
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    protected function generatePage()
    {
        return $this->getPureContent();
    }

    public function getPureContent()
    {
        if (!$this->generatedContent) {
            $this->generatedContent = $this->contentType->convert($this->content, $this->getFile());
        }

        return $this->generatedContent;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(Content $file)
    {
        $this->file = $file;
    }
}
