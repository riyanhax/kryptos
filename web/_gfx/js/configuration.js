uiLanguage = $('html').attr('lang');

uiTranslations = {
    'wyszukaj': {
        en: 'search'
    },
    'od': {
        en: 'from'
    },
    'do': {
        en: 'to'
    },
    'Pierwsza strona' : {
        en: 'First page'
    },
    'Poprzednia strona' : {
        en:'Previous page'
    },
    'Następna strona' : {
        en:'Next page'
    },
    'Ostatnia strona' : {
        en:'Last page'
    },
    'Wybierz stronę' : {
        en:'Select page'
    },
    'Ilość wyników na stronie' : {
        en:'Number of results'
    },
    'Wybierz kolumny' : {
        en:'Choose columns'
    }

            // $(document).ready(function () {
            //     window.firstPageTextsss ="First page";
            //     window.prevPageText ="Previous page";
            //     window.nextPageText ="Next page";
            //     window.lastPageText ="Last page";
            //     window.selectPageText ="Select page";
            //     window.numberOfResultText ="Number of results";
            // });
            
        
};

uiTranslate = function(string) {
    if (uiLanguage === 'pl') {
        return string;
    }

    if (typeof uiTranslations[string] !== 'undefined') {
        return uiTranslations[string][uiLanguage];
    }
};

if (uiLanguage === 'pl') {
    // jquery.timeago.js Polish translation start
    (function() {
        function numpf(n, s, t) {
            // s - 2-4, 22-24, 32-34 ...
            // t - 5-21, 25-31, ...
            var n10 = n % 10;
            if ( (n10 > 1) && (n10 < 5) && ( (n > 20) || (n < 10) ) ) {
                return s;
            } else {
                return t;
            }
        }

        jQuery.timeago.settings.strings = {
            prefixAgo: null,
            prefixFromNow: "za",
            suffixAgo: "temu",
            suffixFromNow: null,
            seconds: "mniej niż minutę",
            minute: "minutę",
            minutes: function(value) { return numpf(value, "%d minuty", "%d minut"); },
            hour: "godzinę",
            hours: function(value) { return numpf(value, "%d godziny", "%d godzin"); },
            day: "dzień",
            days: "%d dni",
            month: "miesiąc",
            months: function(value) { return numpf(value, "%d miesiące", "%d miesięcy"); },
            year: "rok",
            years: function(value) { return numpf(value, "%d lata", "%d lat"); }
        };
    })();
    // jquery.timeago.js Polish translation end
}
