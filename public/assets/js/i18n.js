var	I18n	= {
    t:	function (key, assigns, locale) {
        var	result	= I18n.tb(key, assigns, locale);

        return result ? result : '-- TRANSLATION MISSING: ' + key + ' --';
    },

    tb:	function (key, assigns, locale) {
        var	s	= I18n.translations[locale || I18n.default_locale][key] || false;

        if(s && assigns) {
            for(var i in assigns) {
                search	= '#{' + i + '}';

                while (s.indexOf(search) != -1) {
                    s	= s.replace(search, assigns[i]);
                }
            }
        }

        return s;
    },
    default_locale:	'pt-BR',
    translations: {}
};
