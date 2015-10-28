(function($){

    var typefilters = {
        'images'    : /\.(jpg|jpeg|png|gif|svg)$/i,
        'video'     : /\.(mp4|mov|ogv|webv|flv|avi)$/i,
        'audio'     : /\.(mp3|weba|ogg|wav|flac)$/i,
        'archive'   : /\.(zip|rar|7zip|gz)$/i,
        'documents' : /\.(htm|html|pdf)$/i,
        'text'      : /\.(txt|htm|html|php|css|less|js|json|md|markdown|yaml|xml)$/i
    };

    var coco = {

        createPage: function(root, options) {

            options  = App.$.extend({
                'root': root || '/',
                'types': App.$.extend({
                    'html': {label: 'Html'},
                    'markdown': {label: 'Markdown', ext: 'md'}
                }, COPILOT_PAGE_TYPES)
            }, options)

            var dialog = UIkit.modal.dialog(App.Utils.multiline(function() {/*

                <div riot-view>
                    <div class="uk-modal-header uk-text-large">
                        Create Page
                        <div class="uk-text-muted uk-text-small uk-margin-small-top"><i class="uk-icon-link"></i> { opts.root=='home' || opts.root=='/'  ? '':opts.root }/{ slug.value || slugpreview }</div>
                    </div>
                    <form id="frmNewPage" class="uk-form" onsubmit="{create}">
                        <div class="uk-form-row">
                            <label class="uk-text-small">Title</label>
                            <input name="title" type="text" class="uk-width-1-1 uk-form-large" onkeyup="{ updateSlugPreview }" required>
                        </div>
                        <div class="uk-form-row" if="{opts.root!='home'}">
                            <label class="uk-text-small">Slug</label>
                            <input name="slug" type="text" class="uk-width-1-1 uk-form-large" placeholder="{ slugpreview }" onkeyup="{ update }">
                        </div>
                        <div class="uk-form-row">
                            <label class="uk-text-small">Type</label>
                            <div class="uk-margin-small-top" data-uk-dropdown="\{mode:'click'\}">
                                <a>{ (opts.types[type] && opts.types[type].label) || type }</a>
                                <div class="uk-dropdown">
                                    <ul class="uk-nav uk-nav-dropdown">
                                        <li class="uk-nav-header">Select a type</li>
                                        <li each="{key,val in opts.types}">
                                            <a class="uk-dropdown-close" data-type="{ key }" onclick="{ parent.selectType }">{ val.label || key }</a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="uk-modal-footer uk-text-right">
                        <button class="uk-button uk-button-primary uk-margin-right uk-button-large js-create-button" onclick="jQuery('#frmNewPage').submit()">Create</button>
                        <button class="uk-button uk-button-link uk-button-large uk-modal-close">Cancel</button>
                    </div>

                    <script type="view/script">

                        this.slugpreview = '';
                        this.type = Object.keys(opts.types)[0] || 'html';

                        updateSlugPreview() {
                            this.slugpreview = this.title.value.toLowerCase().replace(/\s/g, '-');
                        };

                        create() {

                            App.callmodule('coco', 'createPage', [opts.root, {
                                title: this.title.value,
                                slug: this.slug ? this.slug.value :'',
                                type : this.type
                            }]).then(function(data) {

                                if (data.result) {
                                    location.href = App.route('/coco/page/'+data.result);
                                }
                            });
                        };

                        selectType(e) {
                            this.type = e.target.getAttribute('data-type');
                        };

                    </script>

                </div>

            */}), {modal:false});

            options.dialog = dialog;

            riot.util.initViews(dialog.element[0], options);

            dialog.show();

            setTimeout(function(){
                dialog.element.find(':input:first').focus();
            }, 100);
        },

        getFileIconCls: function(filename) {

            if (filename.match(typefilters.images)) {

                return 'image';

            } else if(filename.match(typefilters.video)) {

                return 'video';

            } else if(filename.match(typefilters.text)) {

                return 'pencil';

            } else if(filename.match(typefilters.archive)) {

                return 'archive';

            } else {
                return 'file-o';
            }
        }
    };

    App.$.extend(true, App, coco);

    window.coco = coco;

})(jQuery);
