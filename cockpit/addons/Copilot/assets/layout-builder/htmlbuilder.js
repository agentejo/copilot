(function() {

    var Builder = {

        _cache : {},

        components: {
            "heading": {
                label: 'Heading',
                html: '@/addons/Copilot/assets/layout-builder/components/heading/template.html',
                edit: {
                    style: 'font-size'
                },
                icon: '/addons/Copilot/assets/layout-builder/icons/heading.svg'
            },
            "html": {
                label: 'Html',
                html: '@/addons/Copilot/assets/layout-builder/components/html/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/code.svg'
            },
            "text": {
                label: 'Text',
                html: '@/addons/Copilot/assets/layout-builder/components/text/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/text.svg'
            },
            "button": {
                label: 'Button',
                html: '@/addons/Copilot/assets/layout-builder/components/button/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/button.svg'
            },
            "divider": {
                label: 'Divider',
                html: '@/addons/Copilot/assets/layout-builder/components/divider/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/divider.svg'
            },
            "video": {
                label: 'Video',
                html: '@/addons/Copilot/assets/layout-builder/components/video/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/video.svg'
            },
            "audio": {
                label: 'Audio',
                html: '@/addons/Copilot/assets/layout-builder/components/audio/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/audio.svg'
            },
            "image": {
                label: 'Image',
                html: '@/addons/Copilot/assets/layout-builder/components/image/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/image.svg'
            },
            "spacer": {
                label: 'Spacer',
                html: '@/addons/Copilot/assets/layout-builder/components/spacer/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/spacer.svg'
            },
            "map": {
                label: 'Map',
                html: '@/addons/Copilot/assets/layout-builder/components/map/template.html',
                icon: '/addons/Copilot/assets/layout-builder/icons/map-marker.svg'
            },
            "grid": {
                label: 'Grid',
                html: '@/addons/Copilot/assets/layout-builder/components/grid/template.html'
            },
            "team": {
                label: 'Team',
                html: '@/addons/Copilot/assets/layout-builder/components/team/template.html'
            }
        },

        getTemplate: function(name, container) {

            var $this = this;

            if (!this._cache[name]) {

                this._cache[name] = new Promise(function(resolve, reject) {

                    var html = $this.components[name].html;

                    if (html.substr(0,1) == '@') {
                        
                        App.$.get(App.base(html.replace('@', '')), {nc:Math.random()}, function(template) {
                            resolve(template)
                        });

                    } else {
                        resolve(html);
                    }
                });
            }

            return this._cache[name];
        },

        inject: function(component, container, after) {
            
            meta = this.components[component];

            return new Promise(function(resolve) {

                HTMLBuilder.getTemplate(component).then(function(template) {
                
                    var element = App.$(template).attr('data-component', component);

                    element.find('audio,video,img').filter('[src=""]').each(function() {
                        
                        if (this.matches('img')) {

                        } else if(this.matches('audio')) {

                        } else if(this.matches('video')) {
                            
                        }
                    });

                    if (after) {
                        App.$(after).after(element);
                    } else {
                        App.$(container).append(element);
                    }

                    resolve(element);
                });

            });

        },

        injectAfter: function(component, element) {
            return this.inject(component, element.parentNode, element);
        }

    };

    window.HTMLBuilder = Builder;
    App.$(document).trigger('htmlbuilder.init', [Builder]);

})();