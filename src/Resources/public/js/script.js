var HofffRateIt = {};

HofffRateIt.API_ENTRYPOINT = '/rateit';

HofffRateIt.widget = function (element) {
    this.element = element;
    this.rating  = parseFloat(element.getAttribute('data-rating'));
    this.max     = parseInt(element.getAttribute('data-max'));
    this.stars   = [];
    this.icons   = {
        unrated: element.getAttribute('data-icon-unrated'),
        rated: element.getAttribute('data-icon-rated'),
        half: element.getAttribute('data-icon-half')
    };

    this.createStars();
    this.draw(this.rating);

    this.element.addEventListener('mouseout', function () {
        this.draw(this.rating);
    }.bind(this));
};

HofffRateIt.widget.prototype.createStars = function () {
    var star;

    this.element.innerHTML = '';

    for (var i = 1; i <= this.max; i++) {
        star = document.createElement('i');
        star.addEventListener('mouseover', this.createHoverHandler(i),);
        star.addEventListener('click', this.createClickHandler(i));

        this.stars.push(star);
        this.element.appendChild(star);
    }
};

HofffRateIt.widget.prototype.draw = function (value) {
    var star;

    for (var i = 1; i <= this.max; i++) {
        star = this.stars[i-1];

        if (value > (i-0.25)) {
            star.setAttribute('class', this.icons.rated);
        } else if (value > (i-0.75)) {
            star.setAttribute('class', this.icons.half);
        } else {
            star.setAttribute('class', this.icons.unrated);
        }
    }
};

HofffRateIt.widget.prototype.rate = function (value) {
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
    request.send('id=' + this.id + '&type= ' + this.type + '&vote=' + value);
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
        document.attachEvent('onreadystatechange', function() {
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
