<?php

namespace Cocorico\CoreBundle\Utils;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlUtils
{
    public static function purifyBasicHtml(string $data): string
    {
        $purifier = new HTMLPurifier();
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.DefinitionID', 'User Content Filter');
        $config->set('HTML.DefinitionRev', 1);
        $config->set('HTML.Allowed', 'a,strong,em,p,span,img,li,ul,ol,sup,sub,small,big,code,blockquote,h1,h2,h3,h4,h5');
        $config->set('HTML.AllowedAttributes', 'a.href,a.title,span.style,span.class,span.id,p.style,img.src,img.style,img.alt,img.title,img.width,img.height');
        $config->set('AutoFormat.Linkify', true);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        $purified =  $purifier->purify($data, $config);

        // TODO in future: add protection against external links

        return $purified;
    }

}