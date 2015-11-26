<field-wysiwyg>

    <textarea name="input" class="uk-width-1-1" rows="5"></textarea>

    <script>

        var $this     = this,
            lang      = document.documentElement.getAttribute('lang') || 'en',
            languages = ['ar','az','ba','bg','by','ca','cs','da','de','el','eo','es_ar','es','fa','fi','fr','ge','he','hr','hu','id','it','ja','ko','lt','lv','mk','nl','no_NB','pl','pt_br','pt_pt','ro','ru','sl','sq','sr-cir','sr-lat','sv','th','tr','ua','vi','zh_cn','zh_tw'],
            redactor;

        if (opts.cls) {
            App.$(this.input).addClass(opts.cls);
        }

        if (opts.rows) {
            this.input.setAttribute('rows', opts.rows);
        }

        this.value = null;
        this._field = null;

        this.$updateValue = function(value, field) {

            if (this.value != value) {

                this.value = value;

                if (redactor && this._field != field) {
                    redactor.code.set(this.value || '');
                }
            }

            this._field = field;

        }.bind(this);


        this.on('mount', function(){

            var assets = [
                    '/assets/lib/redactor/redactor.min.js',
                    '/assets/lib/redactor/redactor.css',
                ];

            var plugins = [
                '/assets/lib/redactor/plugins/fullscreen/fullscreen.js',
                '/assets/lib/redactor/plugins/fontcolor/fontcolor.js',
                '/assets/lib/redactor/plugins/fontsize/fontsize.js',
                '/assets/lib/redactor/plugins/textdirection/textdirection.js',
                '/assets/lib/redactor/plugins/table/table.js',
                '/assets/lib/redactor/plugins/video/video.js'
            ];

            if (lang != 'en' && languages.indexOf(lang) > -1) {
                plugins.push('/assets/lib/redactor/lang/'+lang+'.js');
            }

            App.assets.require(assets, function() {

                App.assets.require(plugins, function() {

                    initPlugins();

                    this.input.value = this.value;

                    App.$($this.input).redactor({
                        lang: lang,
                        plugins: opts.plugins ||  ['table','textdirection','fontcolor','fontsize','video','fullscreen','imagepicker'],
                        initCallback: function() {
                            redactor = this;
                            App.$(document).trigger('init-wysiwyg-editor', [redactor]);
                        },
                        changeCallback: function() {
                            $this.$setValue(this.code.get(), true);
                            App.$($this.input).trigger('wysiwyg-change', [redactor]);
                        }
                    });

                }.bind(this));

            }.bind(this)).catch(function(){

                // fallback if redactor is not available

                this.input.value = this.value;

                App.$(this.input).css('visibility','').on('change', function() {
                    $this.$setValue(this.value);
                });

            }.bind(this));
        });


        function initPlugins() {

            $.Redactor.prototype.imagepicker = function() {
                return {
        			init: function() {
        				var button = this.button.add('image', 'Image Picker');
                        this.button.addCallback(button, this.imagepicker.select);
        			},
        			select: function() {

                        var $this = this;

                        App.media.select(function(selected) {

                            $this.image.insert('<img src="' + SITE_URL+'/'+selected + '" alt="">');

                        }, { typefilter:'image', pattern: '*.jpg|*.png|*.gif|*.svg' });
        				//$(img).click($.proxy(this.imagemanager.insert, this));


        			},
        			insert: function(e) {

        			}
        		};
        	};

        }

    </script>

</field-wysiwyg>
