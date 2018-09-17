<?php

class ContentExtension extends Extension
{
    protected $metaClasses = [
        'SwiftypeMetaTag_Body',
        'SwiftypeMetaTag_PublishedAt',
        'SwiftypeMetaTag_Tags',
        'SwiftypeMetaTag_Title',
        'SwiftypeMetaTag_UpdatedAt',
        'SwiftypeMetaTag_URL'
    ];

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
