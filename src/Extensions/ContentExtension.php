<?php

class ContentExtension extends Extension
{
    protected $metaClasses = [];

    public function __construct()
    {
        parent::__construct();
        // Get the meta tag default classes from the config
        $this->metaClasses = $this->config()->get('metaClasses');
    }

    public function getSwiftypeMetaTags()
    {
        $metaTags = array();

        foreach ($this->metaClasses as $tagClass) {

            if (!class_exists($tagClass)) {
                continue;
            }

            /**
             * @var SwiftypeMetaTag $r
             */
            $r = new $tagClass();
            $tagsString = $r->getMetaTagsString($this->owner->data());

            if ($tagsString === null) {
                continue;
            }

            $metaTags[] = $tagsString;
        }

        return implode("\r\n", $metaTags);
    }
}
