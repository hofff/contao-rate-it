var RateItBackend =
{
    /**
     * Add the interactive help
     */
    addInteractiveHelp: function () {
        // Links and input elements
        ['div.statisticsbar[title]'].each(function (el) {
            new Tips.Contao($$(el).filter(function (i) {
                return i.title != '';
            }), {
                offset: {x:0, y:26}
            });
        });
    }
};

//Initialize the back end script
window.addEvent('domready', function () {
    RateItBackend.addInteractiveHelp();
});

