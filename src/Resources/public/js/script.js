var HofffRateIt = {};

HofffRateIt.API_ENTRYPOINT = '/rateit';

HofffRateIt.widget = function (element) {
    this.options = {
        icons: {
            rated: '.hofff-rate-it-icon-rated',
            unrated: '.hofff-rate-it-icon-unrated',
            half: '.hofff-rate-it-icon-half'
        },
        selectors: {
            widget: '.hofff-rate-it-widget',
            message: '.hofff-rate-it-message',
        },
        disabledClass: 'hofff-rate-it-disabled'
    };

    this.value   = -1;
    this.element = element;
    this.widget  = element.querySelector(this.options.selectors.widget);
    this.message = element.querySelector(this.options.selectors.message);
    this.rating  = parseFloat(this.widget.getAttribute('data-rating'));
    this.max     = parseInt(this.widget.getAttribute('data-max'));
    this.type    = this.widget.getAttribute('data-type');
    this.id      = this.widget.getAttribute('data-id');
    this.enabled = this.widget.getAttribute('data-enabled') === 'true';
    this.stars   = [];
    this.icons   = {
        unrated: this.widget.querySelector(this.options.icons.unrated),
        rated: this.widget.querySelector(this.options.icons.rated),
        half: this.widget.querySelector(this.options.icons.half)
    };

    console.log(this.icons);

    this.draw(this.rating);

    if (this.enabled) {
        this.widget.addEventListener('mouseout', this.drawCurrentRating.bind(this));
        HofffRateIt.Util.removeClass(this.widget, this.options.disabledClass);
    }
};

HofffRateIt.widget.prototype.draw = function (value) {
    var star;

    if (this.value === value) {
        return;
    }

    this.widget.innerHTML = '';
    this.value = value;

    for (var i = 1; i <= this.max; i++) {
        if (value > (i - 0.25)) {
            star = this.icons.rated.cloneNode(true);
        } else if (value > (i - 0.75)) {
            star = this.icons.half.cloneNode(true);
        } else {
            star = this.icons.unrated.cloneNode(true);
        }

        if (this.enabled) {
            star.addEventListener('mouseover', this.createHoverHandler(i));
            star.addEventListener('click', this.createClickHandler(i));
        }

        this.widget.appendChild(star);
    }
};

HofffRateIt.widget.prototype.drawCurrentRating = function () {
    this.draw(this.rating);
};

HofffRateIt.widget.prototype.rate = function (value) {
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.readyState !== XMLHttpRequest.DONE) {
            return;
        }

        if (request.status === 200) {
            var data = JSON.parse(request.response);
            this.message.innerHTML = data.data.description;
            this.enabled = data.data.enabled === 'true';
            this.rating  = parseFloat(data.data.actRating);
            HofffRateIt.Util.addClass(this.widget, this.options.disabledClass);

            this.drawCurrentRating();
        } else {
            var data = JSON.parse(request.response);
            HofffRateIt.Util.addClass(this.message, 'error');
            this.message.innerHTML = data.title;
        }
    }.bind(this);

    request.open('POST', '/app_dev.php/rateit', true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.send('id=' + this.id + '&type=' + this.type + '&vote=' + (100 / this.max * value));
};

HofffRateIt.widget.prototype.createHoverHandler = function (value) {
    return function () {
        if (this.enabled) {
            this.draw(value);
        }
    }.bind(this);
};

HofffRateIt.widget.prototype.createClickHandler = function (value) {
    return function (event) {
        event.stopPropagation();
        event.preventDefault();

        if (this.enabled) {
            this.rate(value);
        }
    }.bind(this);
};

HofffRateIt.onReady = function ready(fn) {
    if (document.readyState != 'loading') {
        fn();
    } else if (document.addEventListener) {
        document.addEventListener('DOMContentLoaded', fn);
    } else {
        document.attachEvent('onreadystatechange', function () {
            if (document.readyState != 'loading')
                fn();
        });
    }
};

HofffRateIt.Util = {
    classes: function (element) {
        return element.className.split(' ');
    },

    addClass: function (element, cssClass) {
        var classes = HofffRateIt.Util.classes(element);
        var position = classes.indexOf(cssClass);

        if (position < 0) {
            classes.push(cssClass);
        }
    },

    removeClass: function (element, cssClass) {
        var classes = HofffRateIt.Util.classes(element);
        var position = classes.indexOf(cssClass);

        if (position < 0) {
            return;
        }

        classes.splice(position, 1);

        element.className = classes.join(' ');
    }
};

(function () {
    HofffRateIt.onReady(
        function () {
            var widgets = document.getElementsByClassName('hofff-rate-it');

            for (var i = 0; i < widgets.length; i++) {
                new HofffRateIt.widget(widgets.item(i));
            }
        }
    )
})();
