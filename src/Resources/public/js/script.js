var HofffRateIt = HofffRateIt || {};

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
    this.drawing = false;
    this.icons   = {
        unrated: this.widget.querySelector(this.options.icons.unrated),
        rated: this.widget.querySelector(this.options.icons.rated),
        half: this.widget.querySelector(this.options.icons.half)
    };

    this.draw(this.rating);

    if (this.enabled) {
        this.widget.addEventListener('mouseleave', this.handleMouseLeave.bind(this));
        this.widget.addEventListener('mouseover', this.handleMouseOver.bind(this));
        this.widget.addEventListener('click', this.handleClick.bind(this));
        this.widget.classList.remove(this.options.disabledClass);
    }
};

HofffRateIt.widget.prototype.isDrawingAllowed = function () {
    return this.enabled && !this.drawing;
}

HofffRateIt.widget.prototype.draw = function (value) {
    var star;

    if (this.value === value) {
        return;
    }

    this.drawing = true;
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

        star.setAttribute('data-value', i);

        this.widget.appendChild(star);
    }

    this.drawing = false;
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
            this.widget.classList.add(this.options.disabledClass);

            this.drawCurrentRating();
        } else {
            var data = JSON.parse(request.response);
            this.message.classList.add('error');
            this.message.innerHTML = data.title;
        }
    }.bind(this);

    request.open('POST', HofffRateIt.API_ENTRYPOINT, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.send('id=' + this.id + '&type=' + this.type + '&vote=' + (100 / this.max * value));
};

HofffRateIt.widget.prototype.handleMouseLeave = function () {
    if (this.isDrawingAllowed()) {
        this.drawCurrentRating();
    }
}

HofffRateIt.widget.prototype.handleMouseOver = function (event) {
    if (!this.isDrawingAllowed() || event.target === undefined || !event.target.hasAttribute('data-value')) {
        return ;
    }

    this.draw(parseInt(event.target.getAttribute('data-value')));
}

HofffRateIt.widget.prototype.handleClick = function (event) {
    event.stopPropagation();
    event.preventDefault();

    if (!this.isDrawingAllowed() || event.target === undefined || !event.target.hasAttribute('data-value')) {
        return;
    }

    this.rate(parseInt(event.target.getAttribute('data-value')));
}

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
