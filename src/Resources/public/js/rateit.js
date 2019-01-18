var RateItRating;

function doRateIt() {
    if (window.MooTools) {
        var RateItRatings = new Class({

            Implements: Options,

            options: {
                step: 0.1,       /* Schrittweite */
                readonly: false, /* Bewertungen zulassen */
                resetable: false /* Nicht zurücksetzbar */
            },

            initialize: function(options) {

                this.setOptions(options);

                $$('.rateItRating').each(function(el) {
                    this.initMe(el);
                }.bind(this));
            },

            initMe: function(el) {
                //Does this if the browser is NOT IE6. IE6 users don't deserve fancy ratings. >:(
                if (!Browser.ie || Browser.version > 6) {
                    el.id = el.getAttribute('id');
                    el.rateable = el.getAttribute('rel') == 'not-rateable' ? false : true;
                    el.wrapper = el.getElement('.wrapper');
                    el.textEl = el.getElement('.ratingText');
                    el.selected = el.wrapper.getElement('.rateItRating-selected');
                    el.hover = el.wrapper.getElement('.rateItRating-hover');
                    el.widthFx = new Fx.Tween(el.selected, {property:'width', link:'chain'});

                    var backgroundImage = this.getBackgroundImage(el.wrapper);
                    this.options.starwidth = backgroundImage.width;
                    this.options.starheight = backgroundImage.height / 3; // da immer drei Sterne "übereinander" gebraucht werden
                    if (this.options.starwidth === undefined || this.options.starwidth < 16) {
                        this.options.starwidth = 16;
                    }
                    if (this.options.starheight === undefined || this.options.starheight < 16) {
                        this.options.starheight = 16;
                    }

                    this.setBackgroundPosition(el.selected, -1 * this.options.starheight);
                    this.setBackgroundPosition(el.hover, -1 * 2 * this.options.starheight);

                    el.starPercent = this.getStarPercent(el.id);
                    el.ratableId   = this.getRatableId(el.id);
                    el.ratableType = this.getRatableType(el.id);

                    // Maximalwert (=Anzahl Sterne) ermitteln
                    this.options.max = this.getRatableMaxValue(el.id);

                    // Höhe für selected und hover einstellen
                    el.selected.setStyle('height', this.options.starheight);
                    el.hover.setStyle('height', this.options.starheight);

                    // Wrapper-Größe so anpassen, dass alle Sterne angezeigt werden
                    el.wrapper.setStyle('width', this.options.starwidth * this.options.max);
                    el.wrapper.setStyle('height', this.options.starheight);

                    // Breite des rateItRating-selected divs setzen
                    this.fillVote(el.starPercent, el);

                    // Breite f�r rateItRating-selected div ermitteln
                    el.currentFill = this.getFillPercent(el.starPercent);

                    if (el.rateable) {
                        el.mouseCrap = function(e) {
                            var fill = e.event.layerX;
                            if (!fill) {
                                fill = e.event.offsetX;
                            }
                            var fillPercent = this.getVotePercent(fill);
                            var nextStep = Math.ceil((fillPercent / 100) * this.options.max);

                            var w = nextStep * this.options.starwidth;
                            if (el.hover.getStyle('width').toInt() != w) {
                                el.selected.setStyle('display', 'none');
                                el.hover.setStyle('width', Math.min(w, this.options.starwidth * this.options.max));
                                el.hover.setStyle('display', 'block');
                            }

                            var newFill = nextStep / this.options.max * 100;
                            this.fillVote(newFill, el);
                        }.bind(this);

                        el.wrapper.addEvent('mouseenter', function(e) {
                            el.wrapper.addEvent('mousemove', el.mouseCrap);
                        });

                        el.wrapper.addEvent('mouseleave', function(e) {
                            el.removeEvent('mousemove');

                            el.hover.setStyle('width', 0);
                            el.hover.setStyle('display', 'none');
                            el.selected.setStyle('display', 'block');

                            el.widthFx.start(el.currentFill);
                        });

                        el.wrapper.addEvent('click', function(e) {
                            el.currentFill = el.newFill;
                            el.wrapper.removeEvents();
                            el.textEl.oldTxt = el.textEl.get('text');
                            el.textEl.set('html', '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                            el.textEl.addClass('loading');

                            // falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
                            if (typeof($('.mbrateItRating')) != 'undefined' && el.id.indexOf('mb') == 0) {
                                var mbid = el.id;
                                mbid = mbid.replace('mb', '');

                                if (typeof(arrRatings) == 'object') {
                                    for (var ri = 0; ri < arrRatings.length; ri++) {
                                        if (arrRatings[ri].rateItID == mbid) {
                                            arrRatings[ri].rated = true;
                                            arrRatings[ri].width = el.hover.getStyle('width');
                                            break;
                                        }
                                    }
                                }

                                if (typeof($(mbid)) != 'undefined') {
                                    var origWrapper = $(mbid).getElement('.wrapper');
                                    origWrapper.removeEvents();
                                    origWrapper.getElement('.rateItRating-selected').setStyle('display', 'none');
                                    origWrapper.getElement('.rateItRating-hover').setStyle('width', el.hover.getStyle('width'));
                                    origWrapper.getElement('.rateItRating-hover').setStyle('display', 'block');
                                }
                            } else {
                                if (typeof(arrRatings) == 'object') {
                                    for (var ri = 0; ri < arrRatings.length; ri++) {
                                        if (arrRatings[ri].rateItID == el.id) {
                                            arrRatings[ri].rated = true;
                                            arrRatings[ri].width = el.hover.getStyle('width');
                                            break;
                                        }
                                    }
                                }
                            }

                            var votePercent = this.getVotePercent(el.newFill);
                            if (this.options.url != null) {
                                new Request({
                                    url:this.options.url,
                                    onComplete:el.updateText
                                })
                                    .post({vote:votePercent,id:el.ratableId,type:el.ratableType});
                            }
                        }.bind(this));

                        el.updateText = function(text) {
                            error = text.split('ERROR:')[1];
                            el.textEl.removeClass('loading');
                            if (error) { el.showError(error); return false; }
                            el.textEl.set('text', text);

                            // falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
                            if (typeof($('.mbrateItRating')) != 'undefined' && el.id.indexOf('mb') == 0) {
                                var mbid = el.getAttribute('id');
                                mbid = mbid.replace('mb', '');

                                if (typeof(arrRatings) == 'object') {
                                    for (var ri = 0; ri < arrRatings.length; ri++) {
                                        if (arrRatings[ri].rateItID == mbid) {
                                            arrRatings[ri].description = text;
                                            break;
                                        }
                                    }
                                }

                                if (typeof($(mbid)) != 'undefined') {
                                    $(mbid).getElement('.ratingText').set('text', text);
                                }
                            } else {
                                if (typeof(arrRatings) == 'object') {
                                    for (var ri = 0; ri < arrRatings.length; ri++) {
                                        if (arrRatings[ri].rateItID == el.id) {
                                            arrRatings[ri].description = text;
                                            break;
                                        }
                                    }
                                }
                            }
                        };
                    }

                    el.showError = function(error) {
                        el.textEl.addClass('ratingError');
                        el.textEl.set('text', error);
                        (function() {
                            el.textEl.set('text', el.textEl.oldTxt);
                            el.textEl.removeClass('ratingError');
                        }).delay(2000);
                    };
                } else {
                    //Replaces all the fancy with a text description of the votes for IE6.
                    //If you want IE6 users to have something fancier to look at, add it here.
                    el.getElement('.ratingText').inject(el, 'before');
                    el.remove();
                }
            },

            fillVote: function(percent, el) {
                el.newFill = this.getFillPercent(percent);
                if (this.getVotePercent(el.newFill) > 100) { el.newFill = this.getFillPercent(100); }
                el.selected.setStyle('width', el.newFill);
            },

            getStarPercent: function(id) {
                /* Format = anyStringHere-<id>-<float(currentStars)>_(scale);
                 * Example: RateItRatings-5-3_5 //Primary key id = 5, 3/5 stars. */
                var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                if (stars != null) {
                    var score = stars[3].toFloat();
                    var scale = stars[4].toFloat();
                    var percent =  (score / scale) * 100;
                    return percent;
                } else {
                    return 0;
                }
            },

            // Ermittelt die Breite des rateItRating-selected divs
            getFillPercent: function (starPercent) {
                return (starPercent / 100) * (this.options.starwidth * this.options.max);
            },

            // Aus der Breite des rateItRating-selected divs die Prozentzahl ermitteln
            getVotePercent: function(actVote) {
                var starsWidth = this.options.starwidth * this.options.max;
                var percent = (actVote / starsWidth * 100).round(2);
                return percent;
            },

            getRatableId: function(id) {
                var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                return stars != null ? stars[1] : '';
            },

            getRatableType: function(id) {
                var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                return stars != null ? stars[2] : '';
            },

            getRatableMaxValue: function(id) {
                var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                return stars != null ? stars[4].toInt() : 0;
            },

            setBackgroundPosition: function(el, pos) {
                el.setStyle('background-position', '0% ' + pos + 'px');
            },

            getBackgroundImagePath: function(el) {
                return el.getStyle('background-image');
            },

            getBackgroundImage: function(el) {
                var reg_imgFile = /url\s*\(["']?(.*)["']?\)/i;
                var dummy = document.createElement('img');
                var string = this.getBackgroundImagePath(el);
                string = string.match(reg_imgFile)[1];
                string = string.replace('\"', '');
                dummy.src = string;
                return dummy;
            }

        });

        window.addEvent('domready', function(e) {
            RateItRating = new RateItRatings({url:'rateit'});
        });
    } else if (window.jQuery) {
        // the rateit plugin as an Object
        (function() {

            RateItRatings = {

                options: {
                    step: 0.1,       /* Schrittweite */
                    readonly: false, /* Bewertungen zulassen */
                    resetable: false /* Nicht zurücksetzbar */
                },

                // this should be called first before doing anything else
                initialize: function(options) {
                    if (typeof options == 'object' && typeof options['url'] != 'undefined')
                        this.options.url = options['url'];

                    var self = this;
                    jQuery('.rateItRating').each(function(i, element) {
                        self.initMe(element);
                    });

                    return this;
                },

                initMe: function(element) {
                    var self = this;

                    //Does this if the browser is NOT IE6. IE6 users don't deserve fancy ratings. >:(
                    if (!Browser.Engine.trident4) {
                        var el = jQuery(element);
                        el.data('id', el.attr('id'));
                        el.data('rateable', el.attr('rel') == 'not-rateable' ? false : true);
                        el.data('wrapper', el.find('.wrapper'));
                        el.data('textEl', el.find('.ratingText'));
//					el.data('offset', getPosition(element).x);
                        el.data('selected', el.find('.rateItRating-selected'));
                        el.data('hover', el.find('.rateItRating-hover'));

                        jQuery.when(self.getBackgroundImage(el.data('wrapper'))).done(function(backgroundImageSize) {
                            self.options.starwidth = backgroundImageSize[0];
                            self.options.starheight = backgroundImageSize[1] / 3; // da immer drei Sterne "übereinander" gebraucht werden
                        });
                        if (self.options.starwidth === undefined || self.options.starwidth < 16) {
                            self.options.starwidth = 16;
                        }
                        if (self.options.starheight === undefined || self.options.starheight < 16) {
                            self.options.starheight = 16;
                        }

                        self.setBackgroundPosition(el.data('selected'), -1 * self.options.starheight);
                        self.setBackgroundPosition(el.data('hover'), -1 * 2 * self.options.starheight);

                        el.data('starPercent', self.getStarPercent(el.data('id')));
                        el.data('ratableId', self.getRatableId(el.data('id')));
                        el.data('ratableType', self.getRatableType(el.data('id')));

                        // Maximalwert (=Anzahl Sterne) ermitteln
                        self.options.max = self.getRatableMaxValue(el.data('id'));

                        // Höhe für selected und hover einstellen
                        el.data('selected').css('height', self.options.starheight);
                        el.data('hover').css('height', self.options.starheight);

                        // Wrapper-Größe so anpassen, dass alle Sterne angezeigt werden
                        el.data('wrapper').css('width', self.options.starwidth * self.options.max);
                        el.data('wrapper').css('height', self.options.starheight);

                        // Breite des rateItRating-selected divs setzen
                        self.fillVote(el.data('starPercent'), el);

                        // Breite für rateItRating-selected div ermitteln
                        el.data('currentFill', self.getFillPercent(el.data('starPercent')));

                        if (el.data('rateable')) {
                            el.data('wrapper').mouseenter(function(event) {
                                el.data('selected').hide(500, "easeInOutQuad");
                                el.data('hover').show();
                                el.data('wrapper').mousemove({'el': el, 'self': self}, self.mouseCrap);
                            });

                            el.data('wrapper').mouseleave(function(event) {
                                el.data('wrapper').unbind('mousemove');
                                el.data('hover').hide();
                                el.data('selected').show();
                                el.data('selected').animate({width: el.data('currentFill')}, 500);
                            });

                            el.data('wrapper').click(function(event) {
                                el.data('currentFill', el.data('newFill'));
                                el.data('wrapper').unbind();
                                el.data('oldTxt', el.data('textEl').text());
                                el.data('textEl').html('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
                                el.data('textEl').addClass('loading');

                                // falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
                                if (typeof(jQuery('.mbrateItRating')) != 'undefined' && el.data('id').indexOf('mb') == 0) {
                                    var mbid = el.data('id');
                                    mbid = mbid.replace('mb', '');

                                    if (typeof(arrRatings) == 'object') {
                                        for (var ri = 0; ri < arrRatings.length; ri++) {
                                            if (arrRatings[ri].rateItID == mbid) {
                                                arrRatings[ri].rated = true;
                                                arrRatings[ri].width = el.data('hover').css('width');
                                                break;
                                            }
                                        }
                                    }

                                    if (typeof(jQuery('#' + jEscape(mbid))) != 'undefined') {
                                        var origWrapper = jQuery('#' + jEscape(mbid)).find('.wrapper');
                                        origWrapper.unbind();
                                        origWrapper.find('.rateItRating-selected').css('display', 'none');
                                        origWrapper.find('.rateItRating-hover').css('width', el.data('hover').css('width'));
                                        origWrapper.find('.rateItRating-hover').css('display', 'block');
                                    }
                                } else {
                                    if (typeof(arrRatings) == 'object') {
                                        for (var ri = 0; ri < arrRatings.length; ri++) {
                                            if (arrRatings[ri].rateItID == el.data('id')) {
                                                arrRatings[ri].rated = true;
                                                arrRatings[ri].width = el.data('hover').css('width');
                                                break;
                                            }
                                        }
                                    }
                                }

                                var votePercent = self.getVotePercent(el.data('newFill'));
                                if (self.options.url != null) {
                                    jQuery.ajax({
                                        url: self.options.url,
                                        type: 'post',
                                        data: {'vote': votePercent, 'id': el.data('ratableId'), 'type': el.data('ratableType')}
                                    }).done(function(data) {
                                        el.data('updateText')(el, data);
                                    });
                                }
                            });
                        }

                        el.data('updateText', self.updateText);
                    } else {
                        alert("Ich bin ein IE6");
                    }
                },

                fillVote: function(percent, el) {
                    el.data('newFill', this.getFillPercent(percent));
                    if (this.getVotePercent(el.data('newFill')) > 100) { el.data('newFill', this.getFillPercent(100)); }
                    el.data('selected').css('width', el.data('newFill'));
                },

                mouseCrap: function(event) {
                    var el = event.data['el'];
                    var self = event.data['self'];

                    var fill = event.originalEvent.layerX;
                    if (!fill) {
                        fill = event.originalEvent.offsetX;
                    }
                    var fillPercent = self.getVotePercent(fill);
                    var nextStep = Math.ceil((fillPercent / 100) * self.options.max);

                    var w = nextStep * self.options.starwidth;
                    if (parseInt(el.data('hover').css('width')) != w) {
                        el.data('selected').css('display', 'none');
                        el.data('hover').css('width', Math.min(w, self.options.starwidth * self.options.max));
                        el.data('hover').css('display', 'block');
                    }

                    var newFill = nextStep / self.options.max * 100;
                    self.fillVote(newFill, el);
                },

                getStarPercent: function(id) {
                    /* Format = anyStringHere-<id>-<float(currentStars)>_(scale);
                     * Example: RateItRatings-5-3_5 //Primary key id = 5, 3/5 stars. */
                    var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                    if (stars != null) {
                        var score = parseFloat(stars[3]);
                        var scale = parseFloat(stars[4]);
                        var percent =  (score / scale) * 100;
                        return percent;
                    } else {
                        return 0;
                    }
                },

                // Ermittelt die Breite des rateItRating-selected divs
                getFillPercent: function (starPercent) {
                    return (starPercent / 100) * (this.options.starwidth * this.options.max);
                },

                // Aus der Breite des rateItRating-selected divs die Prozentzahl ermitteln
                getVotePercent: function(actVote) {
                    var starsWidth = this.options.starwidth * this.options.max;
                    var percent = (actVote / starsWidth * 100).toFixed(2);
                    return percent;
                },

                getRatableId: function(id) {
                    var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                    return stars != null ? stars[1] : '';
                },

                getRatableType: function(id) {
                    var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                    return stars != null ? stars[2] : '';
                },

                getRatableMaxValue: function(id) {
                    var stars = id.match(/(\d*\|?\d*)-(page|article|ce|module|news|faq|galpic|news4ward)-(\d*\.?\d+)_(\d*\.?\d+)$/);
                    return stars != null ? parseInt(stars[4]) : 0;
                },

                setBackgroundPosition: function(el, pos) {
                    el.css('background-position', '0% ' + pos + 'px');
                },

                getBackgroundImagePath: function(el) {
                    return el.css("background-image");
                },

                getBackgroundImage: function(el) {
                    var dfd = jQuery.Deferred();
                    var backgroundImageSize = new Array();
                    var reg_imgFile = /url\s*\(["']?(.*)["']?\)/i;
                    var string = this.getBackgroundImagePath(el);
                    string = string.match(reg_imgFile)[1];
                    string = string.replace('\"', '');

                    jQuery('<img/>')
                        .attr('src', string)
                        .load(function() {
                            backgroundImageSize.push(this.width);
                            backgroundImageSize.push(this.height);
                            dfd.resolve(backgroundImageSize);
                        });
                    return dfd.promise();
                },

                updateText: function(el, text) {
                    error = text.split('ERROR:')[1];
                    el.data('textEl').removeClass('loading');
                    if (error) { this.RateItRating.showError(el, error); return false; }
                    el.data('textEl').text(text);

                    // falls aus LightBox, entsprechendes ursprüngliches Rating aktualisieren
                    if (typeof(jQuery('.mbrateItRating')) != 'undefined' && el.data('id').indexOf('mb') == 0) {
                        var mbid = el.attr('id');
                        mbid = mbid.replace('mb', '');

                        if (typeof(arrRatings) == 'object') {
                            for (var ri = 0; ri < arrRatings.length; ri++) {
                                if (arrRatings[ri].rateItID == mbid) {
                                    arrRatings[ri].description = text;
                                    break;
                                }
                            }
                        }

                        if (typeof(jQuery('#' + jEscape(mbid))) != 'undefined') {
                            jQuery('#' + jEscape(mbid)).find('.ratingText').text(text);
                        }
                    } else {
                        if (typeof(arrRatings) == 'object') {
                            for (var ri = 0; ri < arrRatings.length; ri++) {
                                if (arrRatings[ri].rateItID == el.data('id')) {
                                    arrRatings[ri].description = text;
                                    break;
                                }
                            }
                        }
                    }
                },

                showError: function(el, error) {
                    el.data('textEl').addClass('ratingError');
                    //oldTxt = el.data('textEl').text();
                    el.data('textEl').text(error);
                    setTimeout(function() {
                        el.data('textEl').text(el.data('oldTxt'));
                        el.data('textEl').removeClass('ratingError');
                    }, 2000);
                }
            };

        })(jQuery);

        jQuery(document).ready(function() {
            jQuery.ajax({
                type: "GET",
                url: "bundles/hofffcontaorateit/js/jquery-ui-effects.custom.min.js",
                dataType: "script",
                async: false,
                cache: true
            });
            jQuery.ajax({
                type: "GET",
                url: "bundles/hofffcontaorateit/js/helper.min.js",
                dataType: "script",
                async: false,
                cache: true
            });
            RateItRating = Object.create(RateItRatings).initialize({url:'rateit'});
        });

        var jEscape = function(jquery) {
            jquery = jquery.replace(new RegExp("\\$", "g"), "\\$");
            jquery = jquery.replace(new RegExp("\~", "g"), "\\~");
            jquery = jquery.replace(new RegExp("\\[", "g"), "\\[");
            jquery = jquery.replace(new RegExp("\\]", "g"), "\\]");
            jquery = jquery.replace(new RegExp("\\|", "g"), "\\|");
            jquery = jquery.replace(new RegExp("\\.", "g"), "\\.");
            jquery = jquery.replace(new RegExp("#", "g"), "\\#");
            return jquery;
        };
    }
}

onReadyRateIt(function() {
    doRateIt();
});
