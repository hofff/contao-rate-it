var HofffRateIt = {};

HofffRateIt.API_ENTRYPOINT = '/rateit';

HofffRateIt.widget = function (element) {
    this.options = {
        icons: {
            rated: '.hofff-rate-it-icon-rated',
            unrated: '.hofff-rate-it-icon-unrated',
            half: '.hofff-rate-it-icon-half'
        }
    };

    this.value = -1;
    this.element = element;
    this.rating = parseFloat(element.getAttribute('data-rating'));
    this.max = parseInt(element.getAttribute('data-max'));
    this.type = element.getAttribute('data-type');
    this.id = element.getAttribute('data-id');
    this.stars = [];
    this.icons = {
        unrated: element.querySelector(this.options.icons.unrated),
        rated: element.querySelector(this.options.icons.rated),
        half: element.querySelector(this.options.icons.half)
    };

    console.log(this);

    this.draw(this.rating);

    this.element.addEventListener('mouseout', function () {
        this.draw(this.rating);
    }.bind(this));
};

HofffRateIt.widget.prototype.draw = function (value) {
    var star;

    if (this.value === value) {
        return;
    }

    this.element.innerHTML = '';
    this.value = value;

    for (var i = 1; i <= this.max; i++) {
        if (value > (i - 0.25)) {
            star = this.icons.rated.cloneNode(true);
        } else if (value > (i - 0.75)) {
            star = this.icons.half.cloneNode(true);
        } else {
            star = this.icons.unrated.cloneNode(true);
        }

        star.addEventListener('mouseover', this.createHoverHandler(i),);
        star.addEventListener('mouseover', this.createHoverHandler(i),);
        star.addEventListener('click', this.createClickHandler(i));

        this.element.appendChild(star);
    }
};

HofffRateIt.widget.prototype.rate = function (value) {
    console.log(value);
    var request = new XMLHttpRequest();

    request.onreadystatechange = function () {
        if (request.status === XMLHttpRequest.DONE) {
            console.log(request);
        } else {
            console.log(request);
            //alert('Es ist ein Fehler aufgetreten');
        }
    };

    request.open('POST', '/app_dev.php/rateit', true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.send('id=' + this.id + '&type=' + this.type + '&vote=' + (100 / this.max * value));
};

HofffRateIt.widget.prototype.createHoverHandler = function (value) {
    return function () {
        this.draw(value);
    }.bind(this);
};

HofffRateIt.widget.prototype.createClickHandler = function (value) {
    return function (event) {
        event.stopPropagation();
        event.preventDefault();

        this.rate(value);
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

(function () {
    HofffRateIt.onReady(
        function () {
            var widgets = document.getElementsByClassName('hofff-rate-it-widget');

            for (var i = 0; i < widgets.length; i++) {
                new HofffRateIt.widget(widgets.item(i));
            }
        }
    )
})();
