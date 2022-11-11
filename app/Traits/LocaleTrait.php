<?php

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\App;

trait LocaleTrait
{
    use JwtTrait;

    /**
     * @throws Exception
     */
    public function setLocale()
    {
        if (!empty(app('request')->input('locale'))) {
            $locale = app('request')->input('locale');
        }
        elseif (!empty(app('request')->headers->get('locale'))) {
            $locale = app('request')->headers->get('locale');
        }
        elseif (!empty($this->jwt('profile')->get('locale'))) {
            $locale = $this->jwt('profile')->get('locale');
        }
        else {
            $locale = env('DEFAULT_LOCALE');
        }

        self::checkRequestedLocale($locale);

        App::setLocale($locale);
    }

    /**
     * @throws Exception
     */
    public function checkRequestedLocale($locale)
    {
        $localesAllowed = explode(',', env('LOCALES_ALLOWED'));

        if (!in_array($locale, $localesAllowed)) {
            throw new Exception('Locale not allowed.');
        }
    }
}
