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
        $config->set('HTML.AllowedElements', 'a,strong,em,p,span,li,ul,ol,sup,sub,small,big,pre,code,blockquote,h1,h2,h3,h4,h5,h6,address');
        $config->set('HTML.AllowedAttributes', 'a.href,a.title,a.target');
        $config->set('AutoFormat.Linkify', true);
        $config->set('AutoFormat.AutoParagraph', true);
        $config->set('AutoFormat.RemoveEmpty', true);

        $purified = $purifier->purify($data, $config);

        // TODO in future: add protection against external links and always lead them through our redirect page to warn users

        return $purified;
    }
}
