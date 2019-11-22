<?php

namespace BBSLab\NovaTranslation\Tools;

use BBSLab\NovaTranslation\NovaTranslationServiceProvider;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;

class TranslationMatrix extends Tool
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        Nova::script(NovaTranslationServiceProvider::PACKAGE_ID, __DIR__.'/../../dist/js/tool.js');
        Nova::style(NovaTranslationServiceProvider::PACKAGE_ID, __DIR__.'/../../dist/css/tool.css');
    }
}
