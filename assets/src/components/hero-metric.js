gapi.analytics.ready(function() {
    gapi.analytics.createComponent('HeroMetric', {
        initialize: function() {
            this.isRendered = false;
        },
        execute: function() {
            if (gapi.analytics.auth.isAuthorized()) {
                this.render();
            } else {
                gapi.analytics.auth.once('success', this.render.bind(this));
            }
        },
        render: function() {
            var opts = this.get();
            if (this.isRendered === false) {
                var container = document.getElementById(opts.container);
                container.innerHTML = this.template;

                gapi.client.analytics.data.ga.get(opts).then(function(response) {
                    value = parseFloat(response.result.totalsForAllResults[opts.metrics]).toFixed(1);
                    value = (opts.mutator ? opts.mutator(value) : value);
                    container.querySelector('.hero-metric-value').innerHTML = value;
                    container.querySelector('.hero-sub-title').innerHTML = opts.title;
                });

                this.isRendered = true;
                this.emit('render');
            }
        },
        template:
          '<div><span class="hero-metric-value"></span><span class="hero-sub-title"></span></div>'
    });
});